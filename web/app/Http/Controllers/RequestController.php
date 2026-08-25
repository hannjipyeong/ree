<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderContainer;
use App\Models\OrderServiceChange;
use App\Models\SubTask;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RequestController extends Controller
{
    private function authorizeOrderAccess(Order $order): void
    {
        $user = Auth::user();
        if ($user && !$user->hasSourceAccess($order->source)) {
            abort(403, 'Akses Ditolak: Akun Anda (' . $user->role_title . ') hanya memiliki izin mengelola order dari source ' . ($user->admin_source ?: 'tertentu') . '.');
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $adminSource = $user ? $user->admin_source : null;

        $query = Order::with(['customer', 'containers', 'subTasks.supir']);

        if ($adminSource) {
            $query->where('source', $adminSource);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('nama_pt', 'like', "%{$search}%")
                  ->orWhere('wilayah', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        if ($request->filled('layanan')) {
            $query->whereHas('subTasks', function ($q) use ($request) {
                $q->where('service_type', $request->layanan);
            });
        }

        $orders = $query->latest()->paginate(10)->withQueryString();
        $supirs = User::where('role', 'supir')->get();

        return view('requests.index', compact('orders', 'supirs', 'adminSource'));
    }

    public function create()
    {
        $user = Auth::user();
        $adminSource = $user ? $user->admin_source : null;
        $customers = User::where('role', 'customer')->get();
        return view('requests.create', compact('customers', 'adminSource'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $adminSource = $user ? $user->admin_source : null;

        $validated = $request->validate([
            'source' => 'required|string',
            'tanggal_order' => 'required|date',
            'nama_pt' => 'required|string',
            'nama_pbm' => 'required|string',
            'no_telp' => 'required|string',
            'wilayah' => 'required|string',
            'lokasi_fasilitas' => 'required|string',
            'jenis_kegiatan' => 'required|string',
            'payload_type' => 'required|string',
            'services' => 'required|array',
            'containers' => 'nullable|array',
            'cargo_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'haulage_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'tkbm_option' => 'nullable|string',
            'jenis_barang' => 'nullable|string',
            'jumlah_tonase' => 'nullable|numeric',
            'nomor_container_cargo' => 'nullable|string',
        ]);

        // If admin has a designated source, force it to prevent tampering
        if ($adminSource) {
            $validated['source'] = $adminSource;
        }

        $cargoPath = null;
        if ($request->hasFile('cargo_file')) {
            $cargoPath = $request->file('cargo_file')->store('uploads/cargo', 'public');
        }

        $haulagePath = null;
        if ($request->hasFile('haulage_file')) {
            $haulagePath = $request->file('haulage_file')->store('uploads/haulage', 'public');
        }

        $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(100, 999);

        $hasAsuransi = in_array('Asuransi', $validated['services']) || $request->boolean('has_asuransi');
        $asuransiValue = $request->input('asuransi_value');

        $order = Order::create([
            'order_number' => $orderNumber,
            'source' => $validated['source'],
            'tanggal_order' => $validated['tanggal_order'],
            'nama_pt' => $validated['nama_pt'],
            'nama_pbm' => $validated['nama_pbm'],
            'no_telp' => $validated['no_telp'],
            'wilayah' => $validated['wilayah'],
            'lokasi_fasilitas' => $validated['lokasi_fasilitas'],
            'jenis_kegiatan' => $validated['jenis_kegiatan'],
            'payload_type' => $validated['payload_type'],
            'jenis_barang' => $request->jenis_barang,
            'jumlah_tonase' => $request->jumlah_tonase,
            'nomor_container_cargo' => $request->nomor_container_cargo,
            'cargo_file_path' => $cargoPath,
            'haulage_file_path' => $haulagePath,
            'tkbm_option' => $request->tkbm_option,
            'has_asuransi' => $hasAsuransi,
            'asuransi_value' => $asuransiValue,
            'status' => 'Submitted',
        ]);

        // Containers (jika payload container)
        if (!empty($request->containers) && is_array($request->containers)) {
            foreach ($request->containers as $c) {
                if (!empty($c['container_number'])) {
                    OrderContainer::create([
                        'order_id' => $order->id,
                        'container_type' => $c['container_type'] ?? "20' GP",
                        'container_size' => $c['container_size'] ?? "20 ft",
                        'container_number' => $c['container_number'],
                    ]);
                }
            }
        }

        // Sub Tasks per selected supir service ONLY (Haulage, LOLO, Penumpukan, TKBM)
        $supirServices = array_filter($validated['services'], fn($s) => $s !== 'Asuransi');
        foreach ($supirServices as $service) {
            $taskCode = strtoupper(substr($service, 0, 3));
            $taskNumber = 'REQ-' . time() . '-' . rand(10, 99) . '-' . $taskCode;

            // Auto assign supir if matching supir exists
            if ($service === 'TKBM') {
                $matchingSupir = User::where('role', 'supir')
                    ->where('supir_type', 'TKBM')
                    ->where('supir_wilayah', $order->wilayah)
                    ->first();
                if (!$matchingSupir) {
                    $matchingSupir = User::where('role', 'supir')
                        ->where('supir_type', 'TKBM')
                        ->where(function($q) use ($order) {
                            $q->where('name', 'like', '%' . $order->wilayah . '%')
                              ->orWhere('email', 'like', '%' . strtolower($order->wilayah) . '%');
                        })
                        ->first();
                }
            } else {
                $matchingSupir = User::where('role', 'supir')->where('supir_type', $service)->first();
            }

            SubTask::create([
                'task_number' => $taskNumber,
                'order_id' => $order->id,
                'service_type' => $service,
                'supir_id' => $matchingSupir ? $matchingSupir->id : null,
                'status' => 'Masuk',
            ]);
        }

        return redirect()->route('requests.index')->with('success', 'Order request berhasil dibuat!');
    }

    public function show(Order $request)
    {
        $this->authorizeOrderAccess($request);
        $order = $request->load(['customer', 'containers.progresses', 'subTasks.supir', 'serviceChanges.changedBy', 'serviceChanges.container']);
        return view('requests.show', compact('order'));
    }

    public function showContainer(Order $request, OrderContainer $container)
    {
        $this->authorizeOrderAccess($request);
        $order = $request->load(['customer', 'subTasks.supir', 'serviceChanges.changedBy', 'serviceChanges.container']);
        $container->load(['progresses.subTask.supir']);
        return view('requests.container_detail', compact('order', 'container'));
    }

    public function updateServices(Request $httpRequest, $request)
    {
        $order = $request instanceof Order ? $request : Order::findOrFail($request);
        $this->authorizeOrderAccess($order);

        $validated = $httpRequest->validate([
            'tkbm_option' => 'nullable|string',
            'added_services' => 'nullable|array',
            'asuransi_value' => 'nullable|numeric',
            'document_name' => 'nullable|string',
            'supporting_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string',
        ]);

        $oldTkbm = $order->tkbm_option;
        $newTkbm = $httpRequest->input('tkbm_option');
        $addedServices = $httpRequest->input('added_services', []);

        // 1. Update Order TKBM option if changed
        if ($newTkbm && $newTkbm !== $oldTkbm) {
            $order->tkbm_option = $newTkbm;
        }

        // 2. Process added services and auto-create sub-tasks if needed
        foreach ($addedServices as $service) {
            if ($service === 'Asuransi') {
                $order->has_asuransi = true;
                if ($httpRequest->filled('asuransi_value')) {
                    $order->asuransi_value = $httpRequest->input('asuransi_value');
                }
            } else {
                $exists = SubTask::where('order_id', $order->id)
                    ->where('service_type', $service)
                    ->exists();

                if (!$exists) {
                    $taskCode = strtoupper(substr($service, 0, 3));
                    $taskNumber = 'REQ-' . time() . '-' . rand(10, 99) . '-' . $taskCode;

                    if ($service === 'TKBM') {
                        $matchingSupir = User::where('role', 'supir')
                            ->where('supir_type', 'TKBM')
                            ->where('supir_wilayah', $order->wilayah)
                            ->first();
                        if (!$matchingSupir) {
                            $matchingSupir = User::where('role', 'supir')
                                ->where('supir_type', 'TKBM')
                                ->where(function($q) use ($order) {
                                    $q->where('name', 'like', '%' . $order->wilayah . '%')
                                      ->orWhere('email', 'like', '%' . strtolower($order->wilayah) . '%');
                                })
                                ->first();
                        }
                    } else {
                        $matchingSupir = User::where('role', 'supir')->where('supir_type', $service)->first();
                    }

                    SubTask::create([
                        'task_number' => $taskNumber,
                        'order_id' => $order->id,
                        'service_type' => $service,
                        'supir_id' => $matchingSupir ? $matchingSupir->id : null,
                        'status' => 'Masuk',
                    ]);
                }
            }
        }

        // 3. Save Order updates
        $order->save();

        // 4. Store supporting document / letter if uploaded
        $docPath = null;
        if ($httpRequest->hasFile('supporting_letter')) {
            $path = $httpRequest->file('supporting_letter')->store('uploads/service_letters', 'public');
            $docPath = 'storage/' . $path;
        }

        // 5. Record change log in OrderServiceChange
        OrderServiceChange::create([
            'order_id' => $order->id,
            'old_tkbm_option' => $oldTkbm,
            'new_tkbm_option' => $newTkbm,
            'added_services' => $addedServices,
            'document_name' => $httpRequest->input('document_name') ?: 'Surat Perubahan Layanan / Lapangan',
            'document_path' => $docPath,
            'notes' => $httpRequest->input('notes'),
            'changed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Layanan order & dokumen surat pendukung berhasil diperbarui!');
    }

    public function cancelContainer(Request $httpRequest, $requestId, $containerId)
    {
        $order = Order::findOrFail($requestId);
        $container = OrderContainer::where('order_id', $order->id)->findOrFail($containerId);

        if ($container->is_cancelled) {
            return back()->with('error', 'Kontainer ini sudah dibatalkan sebelumnya.');
        }

        $container->update(['is_cancelled' => true]);

        // Catat di riwayat perubahan layanan order
        OrderServiceChange::create([
            'order_id' => $order->id,
            'order_container_id' => $container->id,
            'changed_by' => auth()->id(),
            'notes' => 'Kontainer dibatalkan oleh Admin.',
        ]);

        return back()->with('success', 'Kontainer berhasil dibatalkan. Tugas lapangan (supir) untuk kontainer ini telah dinonaktifkan.');
    }

    public function updateContainerServices(Request $httpRequest, $requestId, $containerId)
    {
        $order = Order::findOrFail($requestId);
        $this->authorizeOrderAccess($order);
        $container = OrderContainer::findOrFail($containerId);

        $validated = $httpRequest->validate([
            'tkbm_option' => 'nullable|string',
            'added_services' => 'nullable|array',
            'asuransi_value' => 'nullable|numeric',
            'document_name' => 'nullable|string',
            'supporting_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'sp3kk_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string',
        ]);

        $oldTkbm = $container->tkbm_option ?: $order->tkbm_option;
        $newTkbm = $httpRequest->input('tkbm_option');
        $addedServices = $httpRequest->input('added_services', []);

        $container->tkbm_option = $newTkbm;
        $container->additional_services = array_unique(array_merge($container->additional_services ?: [], $addedServices));
        $container->save();

        foreach ($addedServices as $service) {
            if ($service === 'Asuransi') {
                $order->has_asuransi = true;
                if ($httpRequest->filled('asuransi_value')) {
                    $order->asuransi_value = $httpRequest->input('asuransi_value');
                }
                $order->save();
            } else {
                $subTask = SubTask::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'service_type' => $service,
                    ],
                    [
                        'task_number' => 'REQ-' . time() . '-' . rand(10, 99) . '-' . strtoupper(substr($service, 0, 3)),
                        'supir_id' => User::where('role', 'supir')->where('supir_type', $service)->value('id'),
                        'status' => 'Masuk',
                    ]
                );

                \App\Models\SubTaskContainerProgress::firstOrCreate([
                    'sub_task_id' => $subTask->id,
                    'order_container_id' => $container->id,
                ], [
                    'status' => 'Masuk',
                ]);
            }
        }

        $docPath = null;
        if ($httpRequest->hasFile('supporting_letter')) {
            $path = $httpRequest->file('supporting_letter')->store('uploads/service_letters', 'public');
            $docPath = 'storage/' . $path;
        }

        if ($httpRequest->hasFile('sp3kk_file')) {
            $sp3kkPath = $httpRequest->file('sp3kk_file')->store('uploads/sp3kk_files', 'public');
            $container->sp3kk_file_path = $sp3kkPath;
            $container->save();
        }

        OrderServiceChange::create([
            'order_id' => $order->id,
            'order_container_id' => $container->id,
            'old_tkbm_option' => $oldTkbm,
            'new_tkbm_option' => $newTkbm,
            'added_services' => $addedServices,
            'document_name' => $httpRequest->input('document_name') ?: ('Surat Perubahan Kontainer ' . ($container->container_number ?: ('#' . $container->id))),
            'document_path' => $docPath,
            'notes' => $httpRequest->input('notes'),
            'changed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Layanan & opsi TKBM khusus kontainer ' . ($container->container_number ?: '') . ' berhasil diperbarui!');
    }

    public function destroy(Order $request)
    {
        $this->authorizeOrderAccess($request);
        $request->delete();
        return redirect()->route('requests.index')->with('success', 'Order request berhasil dihapus.');
    }

    public function toggleInvoice(Request $req, \App\Models\SubTaskContainerProgress $progress)
    {
        // Authorize via order
        $order = $progress->container->order;
        $this->authorizeOrderAccess($order);

        if ($req->has('is_invoiced')) {
            $progress->is_invoiced = $req->boolean('is_invoiced');
        } else {
            $progress->is_invoiced = !$progress->is_invoiced;
        }

        if ($progress->is_invoiced) {
            $progress->invoice_number = $req->input('invoice_number') ?: ('INV/' . date('Ymd') . '/' . sprintf('%04d', $progress->id));
            $progress->invoiced_at = now();
        } else {
            $progress->invoice_number = null;
            $progress->invoiced_at = null;
        }

        $progress->save();

        $msg = $progress->is_invoiced
            ? 'Status invoice berhasil diubah menjadi Sudah Terbit (' . $progress->invoice_number . ')!'
            : 'Status invoice berhasil diubah menjadi Belum Terbit.';

        // Return JSON for AJAX requests
        if ($req->ajax() || $req->wantsJson() || $req->header('Accept') === 'application/json') {
            return response()->json([
                'success'        => true,
                'message'        => $msg,
                'is_invoiced'    => $progress->is_invoiced,
                'invoice_number' => $progress->invoice_number,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function togglePnbp(Request $req, OrderContainer $container)
    {
        $order = $container->order;
        $this->authorizeOrderAccess($order);

        $validated = $req->validate([
            'is_pnbp'     => 'nullable',
            'pnbp_number' => 'nullable|string|max:255',
            'pnbp_note'   => 'nullable|string',
        ]);

        if ($req->has('is_pnbp')) {
            $container->is_pnbp = filter_var($req->input('is_pnbp'), FILTER_VALIDATE_BOOLEAN);
        } else {
            $container->is_pnbp = !$container->is_pnbp;
        }

        if ($container->is_pnbp) {
            $container->pnbp_number = $req->input('pnbp_number') ?: ($container->pnbp_number ?: ('PNBP/' . date('Ymd') . '/' . sprintf('%04d', $container->id)));
            $container->pnbp_note = $req->input('pnbp_note') ?: $container->pnbp_note;
            $container->pnbp_completed_at = $container->pnbp_completed_at ?: now();
        } else {
            $container->pnbp_number = null;
            $container->pnbp_note = $req->input('pnbp_note') ?: null;
            $container->pnbp_completed_at = null;
        }

        $container->save();

        $msg = $container->is_pnbp
            ? 'Status PNBP kontainer berhasil disetujui / terbit (' . $container->pnbp_number . ')!'
            : 'Status PNBP kontainer berhasil dibatalkan / belum selesai.';

        // Return JSON for AJAX requests
        if ($req->ajax() || $req->wantsJson() || $req->header('Accept') === 'application/json') {
            return response()->json([
                'success'           => true,
                'message'           => $msg,
                'is_pnbp'           => $container->is_pnbp,
                'pnbp_number'       => $container->pnbp_number,
                'pnbp_note'         => $container->pnbp_note,
                'pnbp_completed_at' => $container->pnbp_completed_at ? $container->pnbp_completed_at->format('d/m/Y H:i') : null,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function toggleOrderPnbp(Request $req, Order $request)
    {
        $this->authorizeOrderAccess($request);

        $validated = $req->validate([
            'is_pnbp'     => 'nullable',
            'pnbp_number' => 'nullable|string|max:255',
            'pnbp_note'   => 'nullable|string',
        ]);

        if ($req->has('is_pnbp')) {
            $request->is_pnbp = filter_var($req->input('is_pnbp'), FILTER_VALIDATE_BOOLEAN);
        } else {
            $request->is_pnbp = !$request->is_pnbp;
        }

        if ($request->is_pnbp) {
            $request->pnbp_number = $req->input('pnbp_number') ?: ($request->pnbp_number ?: ('PNBP/' . date('Ymd') . '/' . sprintf('%04d', $request->id)));
            $request->pnbp_note = $req->input('pnbp_note') ?: $request->pnbp_note;
            $request->pnbp_completed_at = $request->pnbp_completed_at ?: now();
        } else {
            $request->pnbp_number = null;
            $request->pnbp_note = $req->input('pnbp_note') ?: null;
            $request->pnbp_completed_at = null;
        }

        $request->save();

        $msg = $request->is_pnbp
            ? 'Status PNBP muatan Cargo berhasil disetujui / terbit (' . $request->pnbp_number . ')!'
            : 'Status PNBP muatan Cargo berhasil dibatalkan / belum selesai.';

        // Return JSON for AJAX requests
        if ($req->ajax() || $req->wantsJson() || $req->header('Accept') === 'application/json') {
            return response()->json([
                'success'           => true,
                'message'           => $msg,
                'is_pnbp'           => $request->is_pnbp,
                'pnbp_number'       => $request->pnbp_number,
                'pnbp_note'         => $request->pnbp_note,
                'pnbp_completed_at' => $request->pnbp_completed_at ? $request->pnbp_completed_at->format('d/m/Y H:i') : null,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function updateSubTaskStatus(Request $req, SubTask $subTask)
    {
        $validated = $req->validate([
            'status' => 'required|in:Masuk,In,Out,Done,Pending',
            'supir_id' => 'nullable|exists:users,id',
            'note' => 'nullable|string',
            'photo' => 'nullable|image|max:10240',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|max:10240',
            'container_id' => 'nullable|exists:order_containers,id',
            'sp3kk_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if (isset($validated['supir_id'])) {
            $subTask->supir_id = $validated['supir_id'];
        }

        // Handle multiple photos upload or single photo
        $uploadedPaths = [];
        if ($req->hasFile('photos')) {
            foreach ($req->file('photos') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('uploads/supir_proofs', 'public');
                    $uploadedPaths[] = 'storage/' . $path;
                }
            }
        }
        if ($req->hasFile('photo')) {
            $path = $req->file('photo')->store('uploads/supir_proofs', 'public');
            $uploadedPaths[] = 'storage/' . $path;
        }
        $primaryPhotoPath = !empty($uploadedPaths) ? $uploadedPaths[0] : null;

        if (!empty($validated['container_id'])) {
            $container = \App\Models\OrderContainer::find($validated['container_id']);
            if ($container && $req->hasFile('sp3kk_file')) {
                $path = $req->file('sp3kk_file')->store('uploads/sp3kk', 'public');
                $container->sp3kk_file_path = 'storage/' . $path;
                $container->save();
            }

            // Update the container progress specifically
            $progress = \App\Models\SubTaskContainerProgress::where('sub_task_id', $subTask->id)
                ->where('order_container_id', $validated['container_id'])
                ->first();

            if ($progress) {
                $progress->status = $validated['status'];
                if ($validated['status'] === 'In') {
                    if ($validated['note']) $progress->in_note = $validated['note'];
                    if (!empty($uploadedPaths)) {
                        $existing = is_array($progress->in_photos) ? $progress->in_photos : [];
                        $progress->in_photos = array_values(array_unique(array_merge($existing, $uploadedPaths)));
                        $progress->in_photo_path = $progress->in_photos[0] ?? $primaryPhotoPath;
                    }
                    $progress->in_time = now();
                } elseif ($validated['status'] === 'Out') {
                    if ($validated['note']) $progress->out_note = $validated['note'];
                    if (!empty($uploadedPaths)) {
                        $existing = is_array($progress->out_photos) ? $progress->out_photos : [];
                        $progress->out_photos = array_values(array_unique(array_merge($existing, $uploadedPaths)));
                        $progress->out_photo_path = $progress->out_photos[0] ?? $primaryPhotoPath;
                    }
                    $progress->out_time = now();
                } elseif ($validated['status'] === 'Done') {
                    if ($validated['note']) {
                        $progress->done_note = $validated['note'];
                        $progress->out_note = $validated['note'];
                    }
                    if (!empty($uploadedPaths)) {
                        $existingDone = is_array($progress->done_photos) ? $progress->done_photos : [];
                        $progress->done_photos = array_values(array_unique(array_merge($existingDone, $uploadedPaths)));
                        $progress->done_photo_path = $progress->done_photos[0] ?? $primaryPhotoPath;
                        
                        $existingOut = is_array($progress->out_photos) ? $progress->out_photos : [];
                        $progress->out_photos = array_values(array_unique(array_merge($existingOut, $uploadedPaths)));
                        $progress->out_photo_path = $progress->out_photos[0] ?? $primaryPhotoPath;
                    }
                    $progress->done_time = now();
                    if (!$progress->out_time) $progress->out_time = now();
                }
                $progress->save();
            }

            // Find highest status among containers: Done > Out > In
            $allProgress = \App\Models\SubTaskContainerProgress::where('sub_task_id', $subTask->id)->get();
            $anyDone = $allProgress->some(fn($p) => in_array($p->status, ['Done', 'DONE', 'Selesai']));
            $anyOut = $allProgress->some(fn($p) => in_array($p->status, ['Out', 'OUT']));
            $anyIn = $allProgress->some(fn($p) => in_array($p->status, ['In', 'IN']));
            
            if ($anyDone) {
                $subTask->status = 'Done';
                if (!$subTask->done_time) $subTask->done_time = now();
            } else if ($anyOut) {
                $subTask->status = 'Out';
            } else if ($anyIn) {
                $subTask->status = 'In';
            }
        } else {
            // Global update for the SubTask
            $subTask->status = $validated['status'];
            if ($validated['status'] === 'In') {
                if ($validated['note']) $subTask->in_note = $validated['note'];
                if (!empty($uploadedPaths)) {
                    $existing = is_array($subTask->in_photos) ? $subTask->in_photos : [];
                    $subTask->in_photos = array_values(array_unique(array_merge($existing, $uploadedPaths)));
                    $subTask->in_photo_path = $subTask->in_photos[0] ?? $primaryPhotoPath;
                }
                $subTask->in_time = now();
            } elseif ($validated['status'] === 'Out') {
                if ($validated['note']) $subTask->out_note = $validated['note'];
                if (!empty($uploadedPaths)) {
                    $existing = is_array($subTask->out_photos) ? $subTask->out_photos : [];
                    $subTask->out_photos = array_values(array_unique(array_merge($existing, $uploadedPaths)));
                    $subTask->out_photo_path = $subTask->out_photos[0] ?? $primaryPhotoPath;
                }
                $subTask->out_time = now();
            } elseif ($validated['status'] === 'Done') {
                if ($validated['note']) {
                    $subTask->done_note = $validated['note'];
                    $subTask->out_note = $validated['note'];
                }
                if (!empty($uploadedPaths)) {
                    $existingDone = is_array($subTask->done_photos) ? $subTask->done_photos : [];
                    $subTask->done_photos = array_values(array_unique(array_merge($existingDone, $uploadedPaths)));
                    $subTask->done_photo_path = $subTask->done_photos[0] ?? $primaryPhotoPath;

                    $existingOut = is_array($subTask->out_photos) ? $subTask->out_photos : [];
                    $subTask->out_photos = array_values(array_unique(array_merge($existingOut, $uploadedPaths)));
                    $subTask->out_photo_path = $subTask->out_photos[0] ?? $primaryPhotoPath;
                }
                $subTask->done_time = now();
                if (!$subTask->out_time) $subTask->out_time = now();
            }
        }

        $subTask->save();

        // Update Order status
        $order = $subTask->order;
        $allSubTasksDone = $order->subTasks()->get()->every(fn($st) => in_array($st->status, ['Done', 'DONE']));
        if ($allSubTasksDone) {
            $order->status = 'Done';
        } else {
            $order->status = 'In Progress';
        }
        $order->save();

        // Return JSON for AJAX requests (real-time UI), or redirect for standard form
        if ($req->ajax() || $req->wantsJson()) {
            $subTask->load('supir');
            $progress = null;
            if (!empty($validated['container_id'])) {
                $progress = \App\Models\SubTaskContainerProgress::where('sub_task_id', $subTask->id)
                    ->where('order_container_id', $validated['container_id'])
                    ->first();
            }
            return response()->json([
                'success' => true,
                'message' => 'Status & bukti foto berhasil diperbarui!',
                'data' => [
                    'sub_task' => $subTask,
                    'progress' => $progress,
                    'order_status' => $order->status,
                    'updated_at' => now()->format('d M Y, H:i:s'),
                ],
            ]);
        }

        return back()->with('success', 'Status & bukti foto tiket tugas pelaksana lapangan berhasil diperbarui!');
    }

    public function exportPdf($request)
    {
        $order = $request instanceof Order ? $request : Order::findOrFail($request);
        $this->authorizeOrderAccess($order);

        // Requirement 5: Nonaktifkan fungsi Ekspor Surat secara khusus pada request Koperasi
        if (strcasecmp($order->source, 'Koperasi') === 0) {
            return back()->with('error', 'Fungsi Ekspor Surat dinonaktifkan secara khusus pada request Koperasi.');
        }

        $order->load(['customer', 'containers', 'subTasks.supir']);

        $isCargo = strtolower($order->payload_type) === 'cargo';

        $now = Carbon::now();
        $seqNumber = sprintf('%03d', $order->id);
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$now->month] ?? 'I';
        $year = $now->year;

        // Nomor surat: nomor/PBM-PKJ/bulan(romawi)/tahun (contoh: 001/PBM-PKJ/VIII/2026)
        $nomorSurat = "{$seqNumber}/PBM-PKJ/{$romanMonth}/{$year}";

        // Lampiran: "-"
        $lampiran = '-';

        // Perihal
        if ($isCargo) {
            $lokasi = $order->lokasi_fasilitas ? ucwords($order->lokasi_fasilitas) : 'Gudang';
            $perihal = 'Permohonan ' . ucwords($order->jenis_kegiatan ?: 'Penumpukan') . ' ' . $lokasi;
        } else {
            $perihal = 'PERMOHONAN ' . strtoupper($order->jenis_kegiatan ?: 'PENUMPUKAN');
        }

        // Tanggal export (tanggal dibuat saat diexport/enter)
        $tanggalExport = $this->formatIndonesianDate($now);

        // Tanggal penumpukan adalah tanggal dikirim / tanggal order
        $tanggalPenumpukan = $order->tanggal_order 
            ? $this->formatIndonesianDate($order->tanggal_order) 
            : $this->formatIndonesianDate($now);

        // Jenis container (bukan jenis cargo tapi jenis container)
        $jenisContainer = $order->containers->pluck('container_type')->unique()->filter()->implode(', ');
        if (empty($jenisContainer)) {
            $jenisContainer = "20' GP";
        }

        // Gambar Kop Surat & Tanda Tangan Statik
        $kopPath = public_path('images/kop_surat.jpg');
        $kopBase64 = file_exists($kopPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath)) : null;

        $ttdPath = public_path('images/tanda_tangan.jpg');
        $ttdBase64 = file_exists($ttdPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($ttdPath)) : null;

        // Dokumen Manifest untuk Cargo (Halaman 2)
        $manifestBase64 = null;
        if ($isCargo) {
            $possiblePaths = [];
            if (!empty($order->cargo_file_path)) {
                $possiblePaths[] = storage_path('app/public/' . ltrim($order->cargo_file_path, '/'));
                $possiblePaths[] = storage_path('app/public/' . str_replace('storage/', '', ltrim($order->cargo_file_path, '/')));
                $possiblePaths[] = public_path(ltrim($order->cargo_file_path, '/'));
                $possiblePaths[] = public_path('storage/' . ltrim($order->cargo_file_path, '/'));
                $possiblePaths[] = base_path('../' . ltrim($order->cargo_file_path, '/'));
            }
            // Fallback ke contoh manifest di direktori publik / assets
            $possiblePaths[] = public_path('uploads/cargo/sample_manifest.jpg');
            $possiblePaths[] = base_path('../assets/images/WhatsApp Image 2026-08-19 at 04.04.01.jpeg');

            foreach ($possiblePaths as $path) {
                if ($path && file_exists($path) && !is_dir($path)) {
                    $mime = @mime_content_type($path) ?: 'image/jpeg';
                    $manifestBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                    break;
                }
            }
        }

        $pdf = Pdf::loadView('requests.pdf', compact(
            'order',
            'isCargo',
            'nomorSurat',
            'lampiran',
            'perihal',
            'tanggalExport',
            'tanggalPenumpukan',
            'jenisContainer',
            'kopBase64',
            'ttdBase64',
            'manifestBase64'
        ))->setPaper('a4', 'portrait');

        $filename = 'Surat_' . str_replace(['/', '\\', ' '], '_', $nomorSurat) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Requirement 2: Ekspor Data Status Done ke PDF dengan Filter Tanggal
     */
    public function exportDonePdf(Request $request)
    {
        $user = Auth::user();
        $adminSource = $user ? $user->admin_source : null;

        $query = Order::with(['customer', 'containers', 'subTasks.supir'])
            ->where(function ($q) {
                $q->where('status', 'Done')
                  ->orWhereDoesntHave('subTasks', function ($sub) {
                      $sub->where('status', '!=', 'Done');
                  });
            });

        if ($adminSource) {
            $query->where('source', $adminSource);
        } elseif ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_order', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_order', '<=', $request->end_date);
        }

        $orders = $query->latest('tanggal_order')->get();

        $startDateStr = $request->filled('start_date') ? Carbon::parse($request->start_date)->format('d/m/Y') : null;
        $endDateStr = $request->filled('end_date') ? Carbon::parse($request->end_date)->format('d/m/Y') : null;

        if ($startDateStr && $endDateStr) {
            $periodeText = "{$startDateStr} s/d {$endDateStr}";
        } elseif ($startDateStr) {
            $periodeText = "Sejak {$startDateStr}";
        } elseif ($endDateStr) {
            $periodeText = "Hingga {$endDateStr}";
        } else {
            $periodeText = "Semua Periode";
        }

        $filterSource = $adminSource ?: $request->input('source');
        $tanggalCetak = Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB';
        $adminUser = $user ? $user->name : 'Admin Ops';

        $pdf = Pdf::loadView('requests.export_done_pdf', compact(
            'orders',
            'periodeText',
            'filterSource',
            'tanggalCetak',
            'adminUser'
        ))->setPaper('a4', 'landscape');

        $filename = 'Laporan_Order_Done_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Requirement 2: Ekspor Data Status Done ke Excel (CSV UTF-8) dengan Filter Tanggal
     */
    public function exportDoneExcel(Request $request)
    {
        $user = Auth::user();
        $adminSource = $user ? $user->admin_source : null;

        $query = Order::with(['customer', 'containers', 'subTasks.supir'])
            ->where(function ($q) {
                $q->where('status', 'Done')
                  ->orWhereDoesntHave('subTasks', function ($sub) {
                      $sub->where('status', '!=', 'Done');
                  });
            });

        if ($adminSource) {
            $query->where('source', $adminSource);
        } elseif ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_order', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_order', '<=', $request->end_date);
        }

        $orders = $query->latest('tanggal_order')->get();

        $filename = 'Laporan_Order_Done_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header Row
            fputcsv($file, [
                'No', 'No. Order', 'Nama PT', 'No. Container / Ref Cargo', 'Ukuran / Jenis Barang',
                'Haulage IN', 'Haulage OUT', 'LOLO IN', 'LOLO OUT',
                'Penumpukan IN', 'Penumpukan OUT', 'TKBM IN', 'TKBM OUT',
                'Catatan', 'Status Invoice', 'Status PNBP'
            ]);

            $rowNo = 1;
            foreach ($orders as $ord) {
                if ($ord->containers->isNotEmpty()) {
                    foreach ($ord->containers as $c) {
                        $pHaulage = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'Haulage') === 0);
                        $pLolo = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'LOLO') === 0);
                        $pPenumpukan = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'Penumpukan') === 0);
                        $pTkbm = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'TKBM') === 0);

                        $notes = $c->progresses->pluck('in_note')->merge($c->progresses->pluck('out_note'))->merge($c->progresses->pluck('done_note'))->filter()->unique()->implode('; ');

                        $isInvoiced = $c->progresses->contains('is_invoiced', true);
                        $invStatus = $isInvoiced ? 'Sudah Terbit' : 'Belum';
                        $invNumber = $c->progresses->where('is_invoiced', true)->pluck('invoice_number')->filter()->unique()->implode(', ');
                        if ($invNumber) $invStatus .= " ({$invNumber})";

                        $pnbpStatus = $c->is_pnbp ? 'Selesai' : 'Belum';
                        if ($c->pnbp_number) $pnbpStatus .= " ({$c->pnbp_number})";

                        fputcsv($file, [
                            $rowNo++,
                            $ord->order_number,
                            $ord->nama_pt,
                            $c->container_number ?: 'Tanpa No',
                            $c->container_size . ' (' . $c->container_type . ')',
                            $pHaulage && $pHaulage->in_time ? \Carbon\Carbon::parse($pHaulage->in_time)->format('d/m/Y H:i') : '-',
                            $pHaulage && $pHaulage->out_time ? \Carbon\Carbon::parse($pHaulage->out_time)->format('d/m/Y H:i') : '-',
                            $pLolo && $pLolo->in_time ? \Carbon\Carbon::parse($pLolo->in_time)->format('d/m/Y H:i') : '-',
                            $pLolo && $pLolo->out_time ? \Carbon\Carbon::parse($pLolo->out_time)->format('d/m/Y H:i') : '-',
                            $pPenumpukan && $pPenumpukan->in_time ? \Carbon\Carbon::parse($pPenumpukan->in_time)->format('d/m/Y H:i') : '-',
                            $pPenumpukan && $pPenumpukan->out_time ? \Carbon\Carbon::parse($pPenumpukan->out_time)->format('d/m/Y H:i') : '-',
                            $pTkbm && $pTkbm->in_time ? \Carbon\Carbon::parse($pTkbm->in_time)->format('d/m/Y H:i') : '-',
                            $pTkbm && $pTkbm->out_time ? \Carbon\Carbon::parse($pTkbm->out_time)->format('d/m/Y H:i') : '-',
                            $notes ?: '-',
                            $invStatus,
                            $pnbpStatus
                        ]);
                    }
                } else {
                    $stHaulage = $ord->subTasks->firstWhere('service_type', 'Haulage');
                    $stLolo = $ord->subTasks->firstWhere('service_type', 'LOLO');
                    $stPenumpukan = $ord->subTasks->firstWhere('service_type', 'Penumpukan');
                    $stTkbm = $ord->subTasks->firstWhere('service_type', 'TKBM');

                    $notesCargo = $ord->subTasks->pluck('in_note')->merge($ord->subTasks->pluck('out_note'))->merge($ord->subTasks->pluck('done_note'))->filter()->unique()->implode('; ');

                    $invStatus = $ord->is_invoiced ? 'Sudah Terbit' : 'Belum';
                    if ($ord->invoice_number) $invStatus .= " ({$ord->invoice_number})";

                    $pnbpStatus = $ord->is_pnbp ? 'Selesai' : 'Belum';
                    if ($ord->pnbp_number) $pnbpStatus .= " ({$ord->pnbp_number})";

                    fputcsv($file, [
                        $rowNo++,
                        $ord->order_number,
                        $ord->nama_pt,
                        $ord->nomor_container_cargo ?: ($ord->nomor_bl ?: 'Cargo'),
                        ($ord->jenis_barang ?: 'General Cargo') . ($ord->jumlah_tonase ? ' (' . $ord->jumlah_tonase . ' T)' : ''),
                        $stHaulage && $stHaulage->in_time ? \Carbon\Carbon::parse($stHaulage->in_time)->format('d/m/Y H:i') : '-',
                        $stHaulage && $stHaulage->out_time ? \Carbon\Carbon::parse($stHaulage->out_time)->format('d/m/Y H:i') : '-',
                        $stLolo && $stLolo->in_time ? \Carbon\Carbon::parse($stLolo->in_time)->format('d/m/Y H:i') : '-',
                        $stLolo && $stLolo->out_time ? \Carbon\Carbon::parse($stLolo->out_time)->format('d/m/Y H:i') : '-',
                        $stPenumpukan && $stPenumpukan->in_time ? \Carbon\Carbon::parse($stPenumpukan->in_time)->format('d/m/Y H:i') : '-',
                        $stPenumpukan && $stPenumpukan->out_time ? \Carbon\Carbon::parse($stPenumpukan->out_time)->format('d/m/Y H:i') : '-',
                        $stTkbm && $stTkbm->in_time ? \Carbon\Carbon::parse($stTkbm->in_time)->format('d/m/Y H:i') : '-',
                        $stTkbm && $stTkbm->out_time ? \Carbon\Carbon::parse($stTkbm->out_time)->format('d/m/Y H:i') : '-',
                        $notesCargo ?: ($ord->pnbp_note ?: '-'),
                        $invStatus,
                        $pnbpStatus
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function formatIndonesianDate($date)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $d = Carbon::parse($date);
        return $d->day . ' ' . ($months[$d->month] ?? $d->format('F')) . ' ' . $d->year;
    }
}
