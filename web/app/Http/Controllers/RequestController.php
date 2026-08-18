<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderContainer;
use App\Models\SubTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'containers', 'subTasks.supir']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('nama_pt', 'like', "%{$search}%")
                  ->orWhere('wilayah', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(10);
        $supirs = User::where('role', 'supir')->get();

        return view('requests.index', compact('orders', 'supirs'));
    }

    public function create()
    {
        $customers = User::where('role', 'customer')->get();
        return view('requests.create', compact('customers'));
    }

    public function store(Request $request)
    {
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
            'cargo_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'haulage_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'tbkm_option' => 'nullable|string',
        ]);

        $cargoPath = null;
        if ($request->hasFile('cargo_file')) {
            $cargoPath = $request->file('cargo_file')->store('uploads/cargo', 'public');
        }

        $haulagePath = null;
        if ($request->hasFile('haulage_file')) {
            $haulagePath = $request->file('haulage_file')->store('uploads/haulage', 'public');
        }

        $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(100, 999);

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
            'cargo_file_path' => $cargoPath,
            'haulage_file_path' => $haulagePath,
            'tbkm_option' => $request->tbkm_option,
            'status' => 'Submitted',
        ]);

        // Containers
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

        // Sub Tasks per selected service
        foreach ($validated['services'] as $service) {
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
        $order = $request->load(['customer', 'containers', 'subTasks.supir']);
        return view('requests.show', compact('order'));
    }

    public function destroy(Order $request)
    {
        $request->delete();
        return redirect()->route('requests.index')->with('success', 'Order request berhasil dihapus.');
    }

    public function updateSubTaskStatus(Request $req, SubTask $subTask)
    {
        $validated = $req->validate([
            'status' => 'required|in:Masuk,In,Out,Done',
            'supir_id' => 'nullable|exists:users,id',
            'note' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
        ]);

        $subTask->status = $validated['status'];
        if (isset($validated['supir_id'])) {
            $subTask->supir_id = $validated['supir_id'];
        }

        $photoPath = null;
        if ($req->hasFile('photo')) {
            $path = $req->file('photo')->store('uploads/supir_proofs', 'public');
            $photoPath = 'storage/' . $path;
        }

        if ($validated['status'] === 'In') {
            if ($validated['note']) $subTask->in_note = $validated['note'];
            if ($photoPath) $subTask->in_photo_path = $photoPath;
        } elseif ($validated['status'] === 'Out' || $validated['status'] === 'Done') {
            if ($validated['note']) $subTask->out_note = $validated['note'];
            if ($photoPath) $subTask->out_photo_path = $photoPath;
        }

        $subTask->save();

        return back()->with('success', 'Status & bukti foto tiket tugas supir berhasil diperbarui!');
    }
}
