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
            $tasks = SubTask::with([
                'order.containers', 
                'order.customer', 
                'containerProgress.container',
                'order.subTasks.containerProgress'
            ])
                ->where('service_type', $supirType)
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tasks
            ]);
        }

        if ($role === 'customer') {
            $user = $request->user();
            $orders = Order::with(['containers', 'subTasks.supir'])
                ->where('customer_id', $user->id)
                ->latest()
                ->get();
        } else {
            $orders = Order::with(['containers', 'subTasks.supir'])
                ->latest()
                ->get();
        }

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
            'tkbm_option' => 'nullable|string',
            'jenis_barang' => 'nullable|string',
            'jumlah_tonase' => 'nullable|numeric',
            'nomor_container_cargo' => 'nullable|string',
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

        $hasAsuransi = (is_array($services) && in_array('Asuransi', $services)) || $request->boolean('has_asuransi');
        $asuransiValue = $request->input('asuransi_value');

        $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(100, 999);

        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_id' => optional($request->user())->id,
            'source' => $validated['source'],
            'tanggal_order' => $validated['tanggal_order'],
            'nama_pt' => $validated['nama_pt'],
            'nama_pbm' => $validated['nama_pbm'] ?? 'PT Bintang Kepri Jaya',
            'no_telp' => $validated['no_telp'],
            'wilayah' => $validated['wilayah'],
            'lokasi_fasilitas' => $validated['lokasi_fasilitas'],
            'jenis_kegiatan' => $validated['jenis_kegiatan'],
            'payload_type' => $validated['payload_type'],
            'cargo_file_path' => $cargoPath ? Storage::url($cargoPath) : null,
            'haulage_file_path' => $haulagePath ? Storage::url($haulagePath) : null,
            'tkbm_option' => $request->tkbm_option,
            'jenis_barang' => $request->jenis_barang,
            'jumlah_tonase' => $request->jumlah_tonase,
            'nomor_container_cargo' => $request->nomor_container_cargo,
            'has_asuransi' => $hasAsuransi,
            'asuransi_value' => $asuransiValue,
            'status' => 'Submitted',
        ]);

        $createdContainers = [];
        if (is_array($containers)) {
            foreach ($containers as $c) {
                if (!empty($c['container_number'])) {
                    $createdContainers[] = OrderContainer::create([
                        'order_id' => $order->id,
                        'container_type' => $c['container_type'] ?? "20' GP",
                        'container_size' => $c['container_size'] ?? "20 ft",
                        'container_number' => $c['container_number'],
                    ]);
                }
            }
        }

        if (is_array($services)) {
            $supirServices = array_filter($services, fn($s) => $s !== 'Asuransi');
            foreach ($supirServices as $service) {
                $taskCode = strtoupper(substr($service, 0, 3));
                $taskNumber = 'REQ-' . time() . '-' . rand(10, 99) . '-' . $taskCode;

                $matchingSupir = User::where('role', 'supir')->where('supir_type', $service)->first();

                $newSubTask = SubTask::create([
                    'task_number' => $taskNumber,
                    'order_id' => $order->id,
                    'service_type' => $service,
                    'supir_id' => $matchingSupir ? $matchingSupir->id : null,
                    'status' => 'Masuk',
                ]);

                foreach ($createdContainers as $c) {
                    \App\Models\SubTaskContainerProgress::create([
                        'sub_task_id' => $newSubTask->id,
                        'order_container_id' => $c->id,
                        'status' => 'Pending',
                    ]);
                }
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
        $subTask = SubTask::with('order.subTasks.containerProgress')->where('id', $id)->orWhere('task_number', $id)->firstOrFail();

        $validated = $request->validate([
            'action_type' => 'required|in:IN,OUT',
            'note' => 'nullable|string',
            'container_id' => 'nullable|integer',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('uploads/supir_proofs', 'public');
        }

        if (!empty($validated['container_id'])) {
            $progress = \App\Models\SubTaskContainerProgress::where('sub_task_id', $subTask->id)
                ->where('order_container_id', $validated['container_id'])
                ->firstOrFail();

            if ($validated['action_type'] === 'IN') {
                $errorMsg = $this->checkHierarchyAllowed($subTask, $validated['container_id'], 'IN');
                if ($errorMsg) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 400);
                }

                $progress->status = 'In';
                $progress->in_note = $validated['note'];
                $progress->in_time = now();
                if ($photoPath) {
                    $progress->in_photo_path = Storage::url($photoPath);
                }
            } else if ($validated['action_type'] === 'OUT') {
                $errorMsg = $this->checkHierarchyAllowed($subTask, $validated['container_id'], 'OUT');
                if ($errorMsg) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 400);
                }

                $progress->status = 'Out';
                $progress->out_note = $validated['note'];
                $progress->out_time = now();
                if ($photoPath) {
                    $progress->out_photo_path = Storage::url($photoPath);
                }
            }
            $progress->save();

            // Check if all containers for this subtask are out
            $allProgress = \App\Models\SubTaskContainerProgress::where('sub_task_id', $subTask->id)->get();
            $allOut = $allProgress->every(fn($p) => $p->status === 'Out');
            $anyIn = $allProgress->some(fn($p) => $p->status === 'In' || $p->status === 'Out');
            
            if ($allOut && $allProgress->count() > 0) {
                $subTask->status = 'Done';
            } else if ($anyIn && $subTask->status === 'Masuk') {
                $subTask->status = 'In';
            }
            $subTask->save();

            // Update Order status
            $order = $subTask->order;
            $allSubTasksDone = $order->subTasks()->get()->every(fn($st) => $st->status === 'Done');
            if ($allSubTasksDone) {
                $order->status = 'Done';
            } else {
                $order->status = 'In Progress';
            }
            $order->save();
            
        } else {
            // Fallback for Cargo/Global
            if ($validated['action_type'] === 'IN') {
                $subTask->status = 'In';
                $subTask->in_note = $validated['note'];
                $subTask->in_time = now();
                if ($photoPath) {
                    $subTask->in_photo_path = Storage::url($photoPath);
                }
            } else if ($validated['action_type'] === 'OUT') {
                $subTask->status = 'Done';
                $subTask->out_note = $validated['note'];
                $subTask->out_time = now();
                $subTask->done_time = now();
                if ($photoPath) {
                    $subTask->out_photo_path = Storage::url($photoPath);
                }
            }
            $subTask->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Aksi supir berhasil diproses',
            'data' => $subTask->load(['containerProgress.container', 'order.containers'])
        ]);
    }

    private function checkHierarchyAllowed($currentSubTask, $containerId, $actionType)
    {
        $hierarchy = ['Haulage', 'Lolo', 'Penumpukan', 'TKBM'];
        
        // Filter subtasks to only those in hierarchy and order them
        $order = $currentSubTask->order;
        $subTasksInHierarchy = $order->subTasks->filter(function($st) use ($hierarchy) {
            return in_array($st->service_type, $hierarchy);
        })->sortBy(function($st) use ($hierarchy) {
            return array_search($st->service_type, $hierarchy);
        })->values();

        $currentIndex = -1;
        foreach ($subTasksInHierarchy as $idx => $st) {
            if ($st->id === $currentSubTask->id) {
                $currentIndex = $idx;
                break;
            }
        }

        if ($currentIndex === -1) return null; // Not in hierarchy, no constraints

        if ($actionType === 'IN') {
            if ($currentIndex > 0) {
                $prevSubTask = $subTasksInHierarchy[$currentIndex - 1];
                $prevProgress = $prevSubTask->containerProgress->firstWhere('order_container_id', $containerId);
                if (!$prevProgress || !in_array($prevProgress->status, ['In', 'Out'])) {
                    return "Menunggu proses IN dari layanan " . $prevSubTask->service_type . ".";
                }
            }
        } else if ($actionType === 'OUT') {
            if ($currentIndex < count($subTasksInHierarchy) - 1) {
                $nextSubTask = $subTasksInHierarchy[$currentIndex + 1];
                $nextProgress = $nextSubTask->containerProgress->firstWhere('order_container_id', $containerId);
                // If next subtask exists, it must have already finished OUT
                if (!$nextProgress || $nextProgress->status !== 'Out') {
                    return "Menunggu proses OUT dari layanan " . $nextSubTask->service_type . ".";
                }
            }
        }

        return null;
    }
}
