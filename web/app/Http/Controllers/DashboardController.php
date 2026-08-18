<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SubTask;
use App\Models\SubTaskContainerProgress;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $adminSource = $user ? $user->admin_source : null;

        $orderBaseQuery = Order::query();
        if ($adminSource) {
            $orderBaseQuery->where('source', $adminSource);
        }

        $totalOrders = (clone $orderBaseQuery)->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalSupir = User::where('role', 'supir')->count();

        $subTaskBaseQuery = SubTask::query();
        if ($adminSource) {
            $subTaskBaseQuery->whereHas('order', function ($q) use ($adminSource) {
                $q->where('source', $adminSource);
            });
        }
        $totalSubTasks = (clone $subTaskBaseQuery)->count();

        $recentQuery = Order::with(['customer', 'subTasks.supir', 'containers'])->latest();
        if ($adminSource) {
            $recentQuery->where('source', $adminSource);
        }

        if ($request->filled('tiket_status')) {
            $recentQuery->whereHas('subTasks', function ($q) use ($request) {
                $q->where('status', $request->tiket_status);
            });
        }

        $recentOrders = $recentQuery->take(20)->get();

        $subTaskStats = [
            'masuk' => (clone $subTaskBaseQuery)->where('status', 'Masuk')->count(),
            'in'    => (clone $subTaskBaseQuery)->where('status', 'In')->count(),
            'out'   => (clone $subTaskBaseQuery)->where('status', 'Out')->count(),
            'done'  => (clone $subTaskBaseQuery)->where('status', 'Done')->count(),
        ];

        return view('dashboard', compact(
            'totalOrders',
            'totalCustomers',
            'totalSupir',
            'totalSubTasks',
            'recentOrders',
            'subTaskStats',
            'adminSource'
        ));
    }

    /**
     * Ambil semua riwayat bukti IN/OUT (notifikasi) untuk panel lonceng.
     * Menggabungkan bukti dari SubTaskContainerProgress (per-container)
     * dan SubTask tanpa container (Cargo / global).
     */
    public function notifications()
    {
        $user = auth()->user();
        $adminSource = $user ? $user->admin_source : null;

        // 1. Bukti per-container (SubTaskContainerProgress yang sudah ada waktu IN atau OUT)
        $containerProofsQuery = SubTaskContainerProgress::with([
            'subTask.order',
            'container',
        ])
        ->where(function ($q) {
            $q->whereNotNull('in_time')
              ->orWhereNotNull('out_time');
        });

        if ($adminSource) {
            $containerProofsQuery->whereHas('subTask.order', function ($q) use ($adminSource) {
                $q->where('source', $adminSource);
            });
        }

        $containerProofs = $containerProofsQuery
            ->latest('updated_at')
            ->take(100)
            ->get()
            ->flatMap(function ($progress) {
                $items = [];
                $order = optional($progress->subTask)->order;

                if ($progress->in_time) {
                    $items[] = [
                        'type'           => 'IN',
                        'time'           => $progress->in_time,
                        'note'           => $progress->in_note,
                        'photo'          => $progress->in_photo_path,
                        'service_type'   => optional($progress->subTask)->service_type,
                        'container_num'  => optional($progress->container)->container_number,
                        'order_id'       => optional($order)->id,
                        'container_id'   => optional($progress->container)->id,
                        'order_number'   => optional($order)->order_number,
                        'nama_pt'        => optional($order)->nama_pt,
                    ];
                }

                if ($progress->out_time) {
                    $items[] = [
                        'type'           => 'OUT',
                        'time'           => $progress->out_time,
                        'note'           => $progress->out_note,
                        'photo'          => $progress->out_photo_path,
                        'service_type'   => optional($progress->subTask)->service_type,
                        'container_num'  => optional($progress->container)->container_number,
                        'order_id'       => optional($order)->id,
                        'container_id'   => optional($progress->container)->id,
                        'order_number'   => optional($order)->order_number,
                        'nama_pt'        => optional($order)->nama_pt,
                    ];
                }

                return $items;
            });

        // 2. Bukti global / Cargo (SubTask tanpa container, punya in/out photo)
        $globalProofsQuery = SubTask::with('order')
            ->whereDoesntHave('containerProgress')
            ->where(function ($q) {
                $q->whereNotNull('in_photo_path')
                  ->orWhereNotNull('out_photo_path');
            });

        if ($adminSource) {
            $globalProofsQuery->whereHas('order', function ($q) use ($adminSource) {
                $q->where('source', $adminSource);
            });
        }

        $globalProofs = $globalProofsQuery
            ->latest('updated_at')
            ->take(50)
            ->get()
            ->flatMap(function ($subTask) {
                $items = [];
                $order = $subTask->order;

                if ($subTask->in_photo_path) {
                    $items[] = [
                        'type'           => 'IN',
                        'time'           => $subTask->updated_at,
                        'note'           => $subTask->in_note,
                        'photo'          => $subTask->in_photo_path,
                        'service_type'   => $subTask->service_type,
                        'container_num'  => null,
                        'order_id'       => optional($order)->id,
                        'container_id'   => null,
                        'order_number'   => optional($order)->order_number,
                        'nama_pt'        => optional($order)->nama_pt,
                    ];
                }

                if ($subTask->out_photo_path) {
                    $items[] = [
                        'type'           => 'OUT',
                        'time'           => $subTask->updated_at,
                        'note'           => $subTask->out_note,
                        'photo'          => $subTask->out_photo_path,
                        'service_type'   => $subTask->service_type,
                        'container_num'  => null,
                        'order_id'       => optional($order)->id,
                        'container_id'   => null,
                        'order_number'   => optional($order)->order_number,
                        'nama_pt'        => optional($order)->nama_pt,
                    ];
                }

                return $items;
            });

        // Gabungkan & urutkan dari terbaru
        $notifications = $containerProofs
            ->merge($globalProofs)
            ->sortByDesc('time')
            ->values();

        return response()->json([
            'success' => true,
            'total'   => $notifications->count(),
            'data'    => $notifications,
        ]);
    }
}
