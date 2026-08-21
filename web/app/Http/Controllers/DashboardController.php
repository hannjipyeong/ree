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
        $user        = auth()->user();
        $adminSource = $user ? $user->admin_source : null;

        // ── Base queries ──────────────────────────────────────────────
        $orderBaseQuery = Order::query();
        if ($adminSource) {
            $orderBaseQuery->where('source', $adminSource);
        }

        $totalOrders    = (clone $orderBaseQuery)->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalSupir     = User::where('role', 'supir')->count();

        $subTaskBaseQuery = SubTask::query();
        if ($adminSource) {
            $subTaskBaseQuery->whereHas('order', fn ($q) => $q->where('source', $adminSource));
        }
        $totalSubTasks = (clone $subTaskBaseQuery)->count();

        $subTaskStats = [
            'masuk' => (clone $subTaskBaseQuery)->where('status', 'Masuk')->count(),
            'in'    => (clone $subTaskBaseQuery)->where('status', 'In')->count(),
            'out'   => (clone $subTaskBaseQuery)->where('status', 'Out')->count(),
            'done'  => (clone $subTaskBaseQuery)->where('status', 'Done')->count(),
        ];

        // ── Filter params ─────────────────────────────────────────────
        $activeStatus  = $request->input('tiket_status');
        $activeLayanan = $request->input('layanan');
        $dateFrom      = $request->input('date_from');
        $dateTo        = $request->input('date_to');
        $search        = $request->input('search');

        // ── Recent Orders query ───────────────────────────────────────
        $recentQuery = Order::with(['customer', 'subTasks.supir', 'containers'])->latest();

        if ($adminSource) {
            $recentQuery->where('source', $adminSource);
        }

        // Precise status filter: only orders that have AT LEAST ONE sub-task
        // matching the requested status (and optionally the requested service)
        if ($activeStatus) {
            $recentQuery->whereHas('subTasks', function ($q) use ($activeStatus, $activeLayanan) {
                $q->where('status', $activeStatus);
                if ($activeLayanan) {
                    $q->where('service_type', $activeLayanan);
                }
            });
        } elseif ($activeLayanan) {
            // If only layanan filter (no status filter)
            $recentQuery->whereHas('subTasks', fn ($q) => $q->where('service_type', $activeLayanan));
        }

        // Date range filter (on tanggal_order)
        if ($dateFrom) {
            $recentQuery->whereDate('tanggal_order', '>=', $dateFrom);
        }
        if ($dateTo) {
            $recentQuery->whereDate('tanggal_order', '<=', $dateTo);
        }

        // Search filter (order number, PT, PBM, wilayah, lokasi, etc.)
        if ($search) {
            $recentQuery->where(function ($q) use ($search) {
                $q->where('order_number',      'like', "%{$search}%")
                  ->orWhere('nama_pt',         'like', "%{$search}%")
                  ->orWhere('nama_pbm',        'like', "%{$search}%")
                  ->orWhere('source',          'like', "%{$search}%")
                  ->orWhere('wilayah',         'like', "%{$search}%")
                  ->orWhere('lokasi_fasilitas','like', "%{$search}%")
                  ->orWhere('jenis_kegiatan',  'like', "%{$search}%")
                  ->orWhere('no_telp',         'like', "%{$search}%")
                  ->orWhere('invoice_number',  'like', "%{$search}%")
                  ->orWhereHas('subTasks', function ($sq) use ($search) {
                      $sq->where('service_type', 'like', "%{$search}%");
                  });
            });
        }

        $recentOrders = $recentQuery->take(50)->get();

        return view('dashboard', compact(
            'totalOrders',
            'totalCustomers',
            'totalSupir',
            'totalSubTasks',
            'recentOrders',
            'subTaskStats',
            'adminSource',
            'activeStatus',
            'activeLayanan',
            'dateFrom',
            'dateTo',
            'search'
        ));
    }

    /**
     * Ambil semua riwayat bukti IN/OUT (notifikasi) untuk panel lonceng.
     */
    public function notifications()
    {
        $user        = auth()->user();
        $adminSource = $user ? $user->admin_source : null;

        // 1. Bukti per-container
        $containerProofsQuery = SubTaskContainerProgress::with([
            'subTask.order',
            'container',
        ])->where(function ($q) {
            $q->whereNotNull('in_time')->orWhereNotNull('out_time');
        });

        if ($adminSource) {
            $containerProofsQuery->whereHas('subTask.order', fn ($q) => $q->where('source', $adminSource));
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
                        'type'          => 'IN',
                        'time'          => $progress->in_time,
                        'note'          => $progress->in_note,
                        'photo'         => $progress->in_photo_path,
                        'service_type'  => optional($progress->subTask)->service_type,
                        'container_num' => optional($progress->container)->container_number,
                        'order_id'      => optional($order)->id,
                        'container_id'  => optional($progress->container)->id,
                        'order_number'  => optional($order)->order_number,
                        'nama_pt'       => optional($order)->nama_pt,
                    ];
                }
                if ($progress->out_time) {
                    $items[] = [
                        'type'          => 'OUT',
                        'time'          => $progress->out_time,
                        'note'          => $progress->out_note,
                        'photo'         => $progress->out_photo_path,
                        'service_type'  => optional($progress->subTask)->service_type,
                        'container_num' => optional($progress->container)->container_number,
                        'order_id'      => optional($order)->id,
                        'container_id'  => optional($progress->container)->id,
                        'order_number'  => optional($order)->order_number,
                        'nama_pt'       => optional($order)->nama_pt,
                    ];
                }
                return $items;
            });

        // 2. Bukti global / Cargo
        $globalProofsQuery = SubTask::with('order')
            ->whereDoesntHave('containerProgress')
            ->where(function ($q) {
                $q->whereNotNull('in_photo_path')->orWhereNotNull('out_photo_path');
            });

        if ($adminSource) {
            $globalProofsQuery->whereHas('order', fn ($q) => $q->where('source', $adminSource));
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
                        'type'          => 'IN',
                        'time'          => $subTask->in_time ?: $subTask->updated_at,
                        'note'          => $subTask->in_note,
                        'photo'         => $subTask->in_photo_path,
                        'service_type'  => $subTask->service_type,
                        'container_num' => null,
                        'order_id'      => optional($order)->id,
                        'container_id'  => null,
                        'order_number'  => optional($order)->order_number,
                        'nama_pt'       => optional($order)->nama_pt,
                    ];
                }
                if ($subTask->out_photo_path) {
                    $items[] = [
                        'type'          => 'OUT',
                        'time'          => $subTask->out_time ?: $subTask->updated_at,
                        'note'          => $subTask->out_note,
                        'photo'         => $subTask->out_photo_path,
                        'service_type'  => $subTask->service_type,
                        'container_num' => null,
                        'order_id'      => optional($order)->id,
                        'container_id'  => null,
                        'order_number'  => optional($order)->order_number,
                        'nama_pt'       => optional($order)->nama_pt,
                    ];
                }
                return $items;
            });

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

    /**
     * Build the filtered order query shared by both export methods.
     */
    private function buildExportQuery(Request $request)
    {
        $user        = auth()->user();
        $adminSource = $user ? $user->admin_source : null;

        $query = Order::with(['customer', 'containers', 'subTasks.supir'])->latest('tanggal_order');

        if ($adminSource) {
            $query->where('source', $adminSource);
        }

        $activeStatus  = $request->input('tiket_status');
        $activeLayanan = $request->input('layanan');
        $dateFrom      = $request->input('date_from');
        $dateTo        = $request->input('date_to');
        $search        = $request->input('search');

        if ($activeStatus) {
            $query->whereHas('subTasks', function ($q) use ($activeStatus, $activeLayanan) {
                $q->where('status', $activeStatus);
                if ($activeLayanan) {
                    $q->where('service_type', $activeLayanan);
                }
            });
        } elseif ($activeLayanan) {
            $query->whereHas('subTasks', fn ($q) => $q->where('service_type', $activeLayanan));
        }

        if ($dateFrom) {
            $query->whereDate('tanggal_order', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('tanggal_order', '<=', $dateTo);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number',       'like', "%{$search}%")
                  ->orWhere('nama_pt',          'like', "%{$search}%")
                  ->orWhere('nama_pbm',         'like', "%{$search}%")
                  ->orWhere('source',           'like', "%{$search}%")
                  ->orWhere('wilayah',          'like', "%{$search}%")
                  ->orWhere('lokasi_fasilitas', 'like', "%{$search}%")
                  ->orWhere('jenis_kegiatan',   'like', "%{$search}%")
                  ->orWhere('invoice_number',   'like', "%{$search}%")
                  ->orWhereHas('subTasks', function ($sq) use ($search) {
                      $sq->where('service_type', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    /**
     * Export dashboard filtered data as CSV (Excel-compatible).
     */
    public function exportExcel(Request $request)
    {
        $orders   = $this->buildExportQuery($request)->get();
        $filename = 'Dashboard_Export_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($file, [
                'No', 'No. Order', 'Tanggal Order', 'Modul/Source', 'Nama PT',
                'Nama PBM', 'Wilayah', 'Lokasi Fasilitas', 'Jenis Kegiatan',
                'Tiket & Status', 'Status Invoice', 'No. Invoice',
            ]);

            foreach ($orders as $i => $ord) {
                $tiketList = $ord->subTasks->map(fn ($st) => "{$st->service_type}:{$st->status}")->implode(', ');
                fputcsv($file, [
                    $i + 1,
                    $ord->order_number,
                    optional($ord->tanggal_order)->format('d/m/Y'),
                    $ord->source,
                    $ord->nama_pt,
                    $ord->nama_pbm,
                    $ord->wilayah,
                    $ord->lokasi_fasilitas,
                    $ord->jenis_kegiatan,
                    $tiketList,
                    $ord->is_invoiced ? 'Sudah Terbit' : 'Belum Keluar',
                    $ord->invoice_number ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export dashboard filtered data as PDF.
     */
    public function exportPdf(Request $request)
    {
        $orders        = $this->buildExportQuery($request)->get();
        $activeStatus  = $request->input('tiket_status', 'Semua Status');
        $activeLayanan = $request->input('layanan', 'Semua Layanan');
        $dateFrom      = $request->input('date_from');
        $dateTo        = $request->input('date_to');
        $search        = $request->input('search');
        $adminUser     = auth()->user()?->name ?? 'Admin';

        $periodeText = 'Semua Periode';
        if ($dateFrom && $dateTo) {
            $periodeText = date('d/m/Y', strtotime($dateFrom)) . ' s/d ' . date('d/m/Y', strtotime($dateTo));
        } elseif ($dateFrom) {
            $periodeText = 'Sejak ' . date('d/m/Y', strtotime($dateFrom));
        } elseif ($dateTo) {
            $periodeText = 'Hingga ' . date('d/m/Y', strtotime($dateTo));
        }

        $tanggalCetak = now()->translatedFormat('d F Y, H:i') . ' WIB';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard_export_pdf', compact(
            'orders', 'activeStatus', 'activeLayanan', 'periodeText',
            'tanggalCetak', 'adminUser', 'search'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Dashboard_Export_' . date('Ymd_His') . '.pdf');
    }
}

