<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderContainer;
use App\Models\SubTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'supir_type' => $user->supir_type,
            ]
        ]);
    }

    // POST /api/register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'token' => $token,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'supir_type' => null,
            ]
        ]);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    // GET /api/orders
    public function getOrders(Request $request)
    {
        $role = $request->query('role', 'customer');
        $supirType = $request->query('supir_type');

        if ($role === 'supir' && $supirType) {
            $tasks = SubTask::with(['order.containers', 'order.customer'])
                ->where('service_type', $supirType)
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tasks
            ]);
        }

        $orders = Order::with(['containers', 'subTasks.supir'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // POST /api/orders
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|string',
            'tanggal_order' => 'required',
            'nama_pt' => 'required|string',
            'nama_pbm' => 'nullable|string',
            'no_telp' => 'required|string',
            'wilayah' => 'required|string',
            'lokasi_fasilitas' => 'required|string',
            'jenis_kegiatan' => 'required|string',
            'payload_type' => 'required|string',
            'services' => 'required', // Array or JSON string
            'containers' => 'nullable', // Array or JSON string
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

        $services = is_string($validated['services']) ? json_decode($validated['services'], true) : $validated['services'];
        $containers = is_string($request->containers) ? json_decode($request->containers, true) : $request->containers;

        $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(100, 999);

        $order = Order::create([
            'order_number' => $orderNumber,
            'source' => $validated['source'],
            'tanggal_order' => $validated['tanggal_order'],
            'nama_pt' => $validated['nama_pt'],
            'nama_pbm' => $validated['nama_pbm'] ?? 'PT. ABC',
            'no_telp' => $validated['no_telp'],
            'wilayah' => $validated['wilayah'],
            'lokasi_fasilitas' => $validated['lokasi_fasilitas'],
            'jenis_kegiatan' => $validated['jenis_kegiatan'],
            'payload_type' => $validated['payload_type'],
            'cargo_file_path' => $cargoPath ? Storage::url($cargoPath) : null,
            'haulage_file_path' => $haulagePath ? Storage::url($haulagePath) : null,
            'tbkm_option' => $request->tbkm_option,
            'status' => 'Submitted',
        ]);

        if (is_array($containers)) {
            foreach ($containers as $c) {
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

        if (is_array($services)) {
            foreach ($services as $service) {
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

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibuat',
            'data' => $order->load(['containers', 'subTasks'])
        ]);
    }

    // PATCH /api/sub-tasks/{id}/action
    public function updateSubTaskAction(Request $request, $id)
    {
        // Support finding by integer ID or string task_number (e.g. REQ-...)
        $subTask = SubTask::where('id', $id)->orWhere('task_number', $id)->firstOrFail();

        $validated = $request->validate([
            'action_type' => 'required|in:IN,OUT',
            'note' => 'nullable|string',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('uploads/supir_proofs', 'public');
        }

        if ($validated['action_type'] === 'IN') {
            $subTask->status = 'In';
            $subTask->in_note = $validated['note'];
            if ($photoPath) {
                $subTask->in_photo_path = Storage::url($photoPath);
            }
        } else if ($validated['action_type'] === 'OUT') {
            $subTask->status = 'Out';
            $subTask->out_note = $validated['note'];
            if ($photoPath) {
                $subTask->out_photo_path = Storage::url($photoPath);
            }
        }

        $subTask->save();

        return response()->json([
            'success' => true,
            'message' => 'Aksi supir berhasil diproses',
            'data' => $subTask
        ]);
    }
}
