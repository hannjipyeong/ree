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
            $matchingSupir = User::where('role', 'supir')->where('supir_type', $service)->first();

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

                    $matchingSupir = User::where('role', 'supir')->where('supir_type', $service)->first();

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

            // Check if all containers for this subtask are out/done
            $allProgress = \App\Models\SubTaskContainerProgress::where('sub_task_id', $subTask->id)->get();
            $allOut = $allProgress->every(fn($p) => in_array($p->status, ['Out', 'Done', 'DONE', 'OUT']));
            $anyIn = $allProgress->some(fn($p) => in_array($p->status, ['In', 'Out', 'Done', 'IN', 'OUT', 'DONE']));
            
            if ($allOut && $allProgress->count() > 0) {
                $subTask->status = 'Done';
                $subTask->done_time = now();
            } else if ($anyIn && $subTask->status === 'Masuk') {
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
        $source = strtoupper($order->source ?: 'ALL IN');
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$now->month] ?? 'I';
        $year = $now->year;

        // Nomor surat: nomor/sourcenya/bulan(romawi)/tahun
        $nomorSurat = "{$seqNumber}/{$source}/{$romanMonth}/{$year}";

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
                'No',
                'No. Order',
                'Tanggal Order',
                'Modul / Source',
                'Nama PT / Customer',
                'Nama PBM',
                'No. Telepon / PIC',
                'Wilayah Operasional',
                'Lokasi Fasilitas',
                'Jenis Kegiatan',
                'Payload Type',
                'Detail Kontainer / Cargo',
                'Layanan & Tiket SubTask',
                'Status Lapangan',
                'Status Invoice',
                'Nomor Invoice',
                'Tanggal Invoice Diterbitkan',
            ]);

            foreach ($orders as $idx => $ord) {
                // Muatan string
                if (strtolower($ord->payload_type) === 'cargo') {
                    $muatan = 'Cargo: ' . ($ord->jenis_barang ?: 'General') . ($ord->jumlah_tonase ? ' (' . $ord->jumlah_tonase . ' Ton)' : '');
                } else {
                    $containerNums = $ord->containers->pluck('container_number')->filter()->implode(', ');
                    $muatan = 'Container (' . $ord->containers->count() . '): ' . ($containerNums ?: 'Tanpa nomor');
                }

                // Layanan string
                $subTaskList = [];
                foreach ($ord->subTasks as $st) {
                    $supirName = $st->supir ? ' [' . $st->supir->name . ']' : '';
                    $subTaskList[] = "{$st->service_type}: {$st->status}{$supirName}";
                }
                if ($ord->has_asuransi) {
                    $subTaskList[] = 'Asuransi Cargo' . ($ord->asuransi_value ? ' (Rp ' . number_format($ord->asuransi_value, 0, ',', '.') . ')' : '');
                }
                $layananStr = implode('; ', $subTaskList);

                fputcsv($file, [
                    $idx + 1,
                    $ord->order_number,
                    $ord->tanggal_order ? $ord->tanggal_order->format('Y-m-d') : '',
                    $ord->source,
                    $ord->nama_pt,
                    $ord->nama_pbm,
                    $ord->no_telp,
                    $ord->wilayah,
                    $ord->lokasi_fasilitas,
                    $ord->jenis_kegiatan,
                    $ord->payload_type,
                    $muatan,
                    $layananStr,
                    $ord->status,
                    $ord->is_invoiced ? 'Sudah Terbit' : 'Belum Keluar',
                    $ord->invoice_number ?: '-',
                    $ord->invoiced_at ? $ord->invoiced_at->format('Y-m-d H:i') : '-',
                ]);
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
