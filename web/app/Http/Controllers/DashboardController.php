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

        // Count unique ORDERS that have at least one SubTask or ContainerProgress
        // matching each status. This ensures the badge count matches the filtered list.
        $statusGroups = [
            'masuk' => ['Masuk', 'Pending', 'MASUK'],
            'in'    => ['In', 'IN', 'in'],
            'out'   => ['Out', 'OUT', 'out'],
            'done'  => ['Done', 'DONE', 'done', 'Selesai'],
        ];

        $subTaskStats = [];
        foreach ($statusGroups as $key => $statuses) {
            $q = clone $orderBaseQuery;
            $subTaskStats[$key] = $q->where(function ($oq) use ($statuses) {
                $oq->whereHas('subTasks', function ($sq) use ($statuses) {
                    $sq->whereIn('status', $statuses)
                       ->orWhereHas('containerProgress', function ($cpq) use ($statuses) {
                           $cpq->whereIn('status', $statuses);
                       });
                });
            })->count();
        }

        // ── Filter params ─────────────────────────────────────────────
        $activeStatus  = $request->input('tiket_status');
        $activeLayanan = $request->input('layanan');
        $activePayload = $request->input('payload_type');
        $dateFrom      = $request->input('date_from');
        $dateTo        = $request->input('date_to');
        $search        = $request->input('search');

        // ── Recent Orders query ───────────────────────────────────────
        $recentQuery = Order::with(['customer', 'subTasks.supir', 'containers.progresses.subTask'])->latest();

        if ($adminSource) {
            $recentQuery->where('source', $adminSource);
        }

        // Payload type filter (Container vs Cargo)
        if ($activePayload) {
            if ($activePayload === 'Container') {
                $recentQuery->where(function ($q) {
                    $q->where('payload_type', 'like', '%Container%')
                      ->orWhereHas('containers');
                });
            } elseif ($activePayload === 'Cargo') {
                $recentQuery->where(function ($q) {
                    $q->where('payload_type', 'like', '%Cargo%')
                      ->orWhereDoesntHave('containers');
                });
            } elseif ($activePayload === 'Both' || $activePayload === 'Container,Cargo') {
                $recentQuery->where('payload_type', 'like', '%Container%')
                            ->where('payload_type', 'like', '%Cargo%');
            } else {
                $recentQuery->where('payload_type', 'like', "%{$activePayload}%");
            }
        }

        // Precise status filter: matches orders with subtasks or container progress in the given status
        if ($activeStatus) {
            $recentQuery->where(function ($q) use ($activeStatus, $activeLayanan) {
                $q->whereHas('subTasks', function ($sq) use ($activeStatus, $activeLayanan) {
                    $sq->where(function ($stq) use ($activeStatus) {
                        $stq->where('status', $activeStatus)
                            ->orWhere('status', strtolower($activeStatus))
                            ->orWhere('status', strtoupper($activeStatus))
                            ->orWhereHas('containerProgress', function ($cpq) use ($activeStatus) {
                                $cpq->where('status', $activeStatus)
                                    ->orWhere('status', strtolower($activeStatus))
                                    ->orWhere('status', strtoupper($activeStatus));
                            });
                    });
                    if ($activeLayanan) {
                        $sq->where('service_type', $activeLayanan);
                    }
                });
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
            'activePayload',
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

        $query = Order::with(['customer', 'subTasks.supir', 'containers.progresses.subTask'])->latest('tanggal_order');

        if ($adminSource) {
            $query->where('source', $adminSource);
        }

        $activeStatus  = $request->input('tiket_status');
        $activeLayanan = $request->input('layanan');
        $activePayload = $request->input('payload_type');
        $dateFrom      = $request->input('date_from');
        $dateTo        = $request->input('date_to');
        $search        = $request->input('search');

        // Payload type filter
        if ($activePayload) {
            if ($activePayload === 'Container') {
                $query->where(function ($q) {
                    $q->where('payload_type', 'like', '%Container%')
                      ->orWhereHas('containers');
                });
            } elseif ($activePayload === 'Cargo') {
                $query->where(function ($q) {
                    $q->where('payload_type', 'like', '%Cargo%')
                      ->orWhereDoesntHave('containers');
                });
            } elseif ($activePayload === 'Both' || $activePayload === 'Container,Cargo') {
                $query->where('payload_type', 'like', '%Container%')
                      ->where('payload_type', 'like', '%Cargo%');
            } else {
                $query->where('payload_type', 'like', "%{$activePayload}%");
            }
        }

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

        $payloadFilter = $request->input('payload_type');

        $callback = function () use ($orders, $payloadFilter) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            $containerOrders = $orders->filter(fn($o) => $o->containers->isNotEmpty());
            $cargoOrders     = $orders->filter(fn($o) => $o->containers->isEmpty());

            // ─── SECTION 1: CONTAINER ──────────────────────────────────────────────
            $showContainer = !$payloadFilter || strtolower($payloadFilter) === 'container';
            if ($showContainer && $containerOrders->isNotEmpty()) {
                fputcsv($file, ['REKAPITULASI LAYANAN KONTAINER (HAULAGE, LOLO, PENUMPUKAN, TKBM)']);
                fputcsv($file, [
                    'No', 'No. Order', 'Nama PT', 'No. Container', 'Ukuran / Tipe',
                    'Haulage IN', 'Haulage OUT',
                    'LOLO IN', 'LOLO OUT',
                    'Penumpukan IN', 'Penumpukan OUT',
                    'TKBM IN', 'TKBM OUT',
                    'Catatan', 'Status Invoice', 'Status PNBP'
                ]);

                $rowNo = 1;
                foreach ($containerOrders as $ord) {
                    $isFirst = true;
                    foreach ($ord->containers as $c) {
                        $pHaulage    = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'Haulage') === 0);
                        $pLolo       = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'LOLO') === 0);
                        $pPenumpukan = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'Penumpukan') === 0);
                        $pTkbm       = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'TKBM') === 0);

                        $notes     = $c->progresses->pluck('in_note')->merge($c->progresses->pluck('out_note'))->merge($c->progresses->pluck('done_note'))->filter()->unique()->implode('; ');
                        $isInvoiced = $c->progresses->contains('is_invoiced', true);
                        $invStatus  = $isInvoiced ? 'Sudah Terbit' : 'Belum';
                        $invNumber  = $c->progresses->where('is_invoiced', true)->pluck('invoice_number')->filter()->unique()->implode(', ');
                        if ($invNumber) $invStatus .= " ({$invNumber})";
                        $pnbpStatus = $c->is_pnbp ? 'Selesai' : 'Belum';
                        if ($c->pnbp_number) $pnbpStatus .= " ({$c->pnbp_number})";

                        fputcsv($file, [
                            $isFirst ? $rowNo++ : '',
                            $isFirst ? $ord->order_number : '',
                            $isFirst ? $ord->nama_pt : '',
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
                            $pnbpStatus,
                        ]);
                        $isFirst = false;
                    }
                }
                fputcsv($file, []); // blank separator row
            }

            // ─── SECTION 2: CARGO ──────────────────────────────────────────────────
            $showCargo = !$payloadFilter || strtolower($payloadFilter) === 'cargo';
            if ($showCargo && $cargoOrders->isNotEmpty()) {
                fputcsv($file, ['REKAPITULASI LAYANAN CARGO (MUATAN BEBAS / GENERAL CARGO)']);
                fputcsv($file, [
                    'No', 'No. Order', 'Nama PT',
                    'Jenis Barang', 'Jml Barang', 'Tonase',
                    'No BL', 'Vessel', 'Voyage', 'No Surat Jalan', 'No BP',
                    'Waktu IN', 'Waktu OUT / Selesai',
                    'Catatan', 'Status Invoice', 'Status PNBP'
                ]);

                $gRowNo = 1;
                foreach ($cargoOrders as $ord) {
                    $inTimes   = $ord->subTasks->pluck('in_time')->filter();
                    $outTimes  = $ord->subTasks->pluck('out_time')->filter();
                    $doneTimes = $ord->subTasks->pluck('done_time')->filter();

                    $earliestIn = $inTimes->isNotEmpty() ? \Carbon\Carbon::parse($inTimes->min())->format('d/m/Y H:i') : '-';
                    $latestOut  = $outTimes->isNotEmpty()
                        ? \Carbon\Carbon::parse($outTimes->max())->format('d/m/Y H:i')
                        : ($doneTimes->isNotEmpty() ? \Carbon\Carbon::parse($doneTimes->max())->format('d/m/Y H:i') : '-');

                    $notesCargo = $ord->subTasks->pluck('in_note')->merge($ord->subTasks->pluck('out_note'))->merge($ord->subTasks->pluck('done_note'))->filter()->unique()->implode('; ');
                    $invStatus  = $ord->is_invoiced ? 'Sudah Terbit' : 'Belum';
                    if ($ord->invoice_number) $invStatus .= " ({$ord->invoice_number})";
                    $pnbpStatus = $ord->is_pnbp ? 'Selesai' : 'Belum';
                    if ($ord->pnbp_number) $pnbpStatus .= " ({$ord->pnbp_number})";

                    fputcsv($file, [
                        $gRowNo++,
                        $ord->order_number,
                        $ord->nama_pt,
                        $ord->jenis_barang ?: 'General Cargo',
                        $ord->jumlah_barang ?: '-',
                        $ord->jumlah_tonase ? $ord->jumlah_tonase . ' T' : '-',
                        $ord->nomor_bl ?: '-',
                        $ord->vessel ?: '-',
                        $ord->voyage ?: '-',
                        $ord->no_surat_jalan ?: '-',
                        $ord->no_bp ?: '-',
                        $earliestIn,
                        $latestOut,
                        $notesCargo ?: ($ord->pnbp_note ?: '-'),
                        $invStatus,
                        $pnbpStatus,
                    ]);
                }
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
        $activePayload = $request->input('payload_type', 'Semua Tipe');
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
            'orders', 'activeStatus', 'activeLayanan', 'activePayload', 'periodeText',
            'tanggalCetak', 'adminUser', 'search'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Dashboard_Export_' . date('Ymd_His') . '.pdf');
    }
}

