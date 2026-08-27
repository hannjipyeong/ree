<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Order Selesai (Done) - PT. Bintang Kepri Jaya</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 12px 15px 15px 15px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 7.5px;
            color: #0f172a;
            background: #ffffff;
            line-height: 1.2;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 8px;
        }
        .meta-table {
            width: 100%;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            font-size: 7.5px;
        }
        .meta-value {
            color: #0f172a;
            font-weight: 600;
            font-size: 8px;
        }
        
        .section-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #1e3a8a;
            background: #f1f5f9;
            border-left: 3px solid #1e3a8a;
            padding: 3px 6px;
            margin-top: 8px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        table.data-table th, table.data-table td {
            border: 0.5px solid #94a3b8;
            padding: 3.5px 3px;
            font-size: 7px;
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        table.data-table thead tr:first-child th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        table.data-table thead tr:nth-child(2) th {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        table.data-table thead tr:nth-child(3) th {
            background-color: #3b82f6;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 6.5px;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-done {
            background-color: #dcfce7;
            color: #15803d;
            border: 0.5px solid #86efac;
        }
        .badge-pending {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 0.5px solid #fca5a5;
        }
        .footer {
            margin-top: 10px;
            border-top: 0.5px solid #e2e8f0;
            padding-top: 4px;
            font-size: 7px;
            color: #94a3b8;
            text-align: right;
        }
        .no-data {
            text-align: center;
            padding: 10px;
            color: #94a3b8;
            font-style: italic;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="title">PT. BINTANG KEPRI JAYA</div>
                <div class="subtitle">{{ $pdfTitle ?? 'Laporan Monitoring & Rekapitulasi Order Selesai (Status: DONE)' }}</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div style="font-size: 8px; color: #475569;">Dicetak pada: <strong>{{ $tanggalCetak }}</strong></div>
                <div style="font-size: 8px; color: #475569;">Oleh: <strong>{{ $adminUser }}</strong></div>
            </td>
        </tr>
    </table>

    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;">
                    <span class="meta-label">Periode Tanggal:</span><br>
                    <span class="meta-value">{{ $periodeText }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="meta-label">Filter Source:</span><br>
                    <span class="meta-value">{{ $filterSource ?: 'Semua Modul' }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="meta-label">Total Order Selesai:</span><br>
                    <span class="meta-value">{{ $orders->count() }} Order</span>
                </td>
                <td style="width: 25%;">
                    <span class="meta-label">Status Invoice:</span><br>
                    <span class="meta-value">
                        {{ $orders->where('is_invoiced', true)->count() }} Terbit / {{ $orders->where('is_invoiced', false)->count() }} Belum
                    </span>
                </td>
            </tr>
        </table>
    </div>

    @php
        $containerOrders = $orders->filter(fn($o) => $o->containers->isNotEmpty());
        $cargoOrders = $orders->filter(fn($o) => str_contains(strtolower($o->payload_type), 'cargo') || $o->containers->isEmpty());
    @endphp

    <!-- 1. REKAPITULASI ORDER KONTAINER (DONE) -->
    <div class="section-title">
        1. Rekapitulasi Order Kontainer (Haulage, LOLO, Penumpukan, TKBM)
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="3" style="width: 2.5%;">No</th>
                <th rowspan="3" style="width: 7.5%;">No Order</th>
                <th rowspan="3" style="width: 10%;">Nama PT</th>
                <th rowspan="3" style="width: 8.5%;">No Container</th>
                <th rowspan="3" style="width: 6.5%;">Ukuran / Tipe</th>
                <th colspan="8" style="text-align: center;">Layanan & Progress Waktu Lapangan</th>
                <th rowspan="3" style="width: 10%;">Catatan</th>
                <th rowspan="3" style="width: 6.5%;">Asuransi</th>
                <th rowspan="3" style="width: 6.5%;">Status Invoice</th>
                <th rowspan="3" style="width: 6%;">Status PNBP</th>
            </tr>
            <tr>
                <th colspan="2" style="width: 9%;">Haulage</th>
                <th colspan="2" style="width: 9%;">LOLO</th>
                <th colspan="2" style="width: 9%;">Penumpukan</th>
                <th colspan="2" style="width: 9%;">TKBM</th>
            </tr>
            <tr>
                <th style="width: 4.5%;">IN</th>
                <th style="width: 4.5%;">OUT</th>
                <th style="width: 4.5%;">IN</th>
                <th style="width: 4.5%;">OUT</th>
                <th style="width: 4.5%;">IN</th>
                <th style="width: 4.5%;">OUT</th>
                <th style="width: 4.5%;">IN</th>
                <th style="width: 4.5%;">OUT</th>
            </tr>
        </thead>
        <tbody>
            @php $cRow = 1; @endphp
            @forelse($containerOrders as $ord)
                @php $containerCount = $ord->containers->count(); @endphp
                @foreach($ord->containers as $idx => $c)
                    @php
                        $pHaulage = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'Haulage') === 0);
                        $pLolo = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'LOLO') === 0);
                        $pPenumpukan = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'Penumpukan') === 0);
                        $pTkbm = $c->progresses->first(fn($p) => $p->subTask && strcasecmp($p->subTask->service_type, 'TKBM') === 0);

                        $haulageIn = $pHaulage && $pHaulage->in_time ? \Carbon\Carbon::parse($pHaulage->in_time)->format('d/m/y H:i') : ($pHaulage && $pHaulage->in_photo_path ? '✓' : '-');
                        $haulageOut = $pHaulage && $pHaulage->out_time ? \Carbon\Carbon::parse($pHaulage->out_time)->format('d/m/y H:i') : ($pHaulage && $pHaulage->out_photo_path ? '✓' : '-');

                        $loloIn = $pLolo && $pLolo->in_time ? \Carbon\Carbon::parse($pLolo->in_time)->format('d/m/y H:i') : ($pLolo && $pLolo->in_photo_path ? '✓' : '-');
                        $loloOut = $pLolo && $pLolo->out_time ? \Carbon\Carbon::parse($pLolo->out_time)->format('d/m/y H:i') : ($pLolo && $pLolo->out_photo_path ? '✓' : '-');

                        $penumpukanIn = $pPenumpukan && $pPenumpukan->in_time ? \Carbon\Carbon::parse($pPenumpukan->in_time)->format('d/m/y H:i') : ($pPenumpukan && $pPenumpukan->in_photo_path ? '✓' : '-');
                        $penumpukanOut = $pPenumpukan && $pPenumpukan->out_time ? \Carbon\Carbon::parse($pPenumpukan->out_time)->format('d/m/y H:i') : ($pPenumpukan && $pPenumpukan->out_photo_path ? '✓' : '-');

                        $tkbmIn = $pTkbm && $pTkbm->in_time ? \Carbon\Carbon::parse($pTkbm->in_time)->format('d/m/y H:i') : ($pTkbm && $pTkbm->in_photo_path ? '✓' : '-');
                        $tkbmOut = $pTkbm && $pTkbm->out_time ? \Carbon\Carbon::parse($pTkbm->out_time)->format('d/m/y H:i') : ($pTkbm && $pTkbm->out_photo_path ? '✓' : '-');

                        $tkbmOpt = $c->tkbm_option ?: ($ord->tkbm_option ?? '');
                        $tkbmBadge = str_contains(strtolower($tkbmOpt), 'forklift') ? 'MP+Forklift' : (strtolower($tkbmOpt) == 'man power' ? 'MP' : '');

                        // Notes combined (Flexible wrap)
                        $notes = $c->progresses->pluck('in_note')->merge($c->progresses->pluck('out_note'))->merge($c->progresses->pluck('done_note'))->filter()->unique()->implode('; ');

                        // Invoice Status
                        $isInvoiced = $c->progresses->contains('is_invoiced', true);
                        $invNumbers = $c->progresses->where('is_invoiced', true)->pluck('invoice_number')->filter()->unique()->implode(', ');

                        // PNBP Status
                        $isPnbp = (bool) $c->is_pnbp;
                        $pnbpNum = $c->pnbp_number;
                    @endphp
                    <tr>
                        @if($idx === 0)
                            <td rowspan="{{ $containerCount }}" style="text-align: center; vertical-align: middle;">{{ $cRow++ }}</td>
                            <td rowspan="{{ $containerCount }}" style="vertical-align: middle;"><strong>{{ $ord->order_number }}</strong></td>
                            <td rowspan="{{ $containerCount }}" style="vertical-align: middle;">{{ $ord->nama_pt }}</td>
                        @endif
                        <td><strong>{{ $c->container_number ?: 'Tanpa No' }}</strong></td>
                        <td>{{ $c->container_size }} ({{ $c->container_type }})</td>
                        
                        <!-- Haulage IN/OUT -->
                        <td style="text-align: center;">{{ $haulageIn }}</td>
                        <td style="text-align: center;">{{ $haulageOut }}</td>
                        
                        <!-- LOLO IN/OUT -->
                        <td style="text-align: center;">{{ $loloIn }}</td>
                        <td style="text-align: center;">{{ $loloOut }}</td>

                        <!-- Penumpukan IN/OUT -->
                        <td style="text-align: center;">{{ $penumpukanIn }}</td>
                        <td style="text-align: center;">{{ $penumpukanOut }}</td>

                        <!-- TKBM IN/OUT -->
                        <td style="text-align: center;">
                            {{ $tkbmIn }}
                            @if($tkbmBadge && $tkbmIn != '-')
                                <div style="font-size: 5.5px; color: #b45309; font-weight: bold;">{{ $tkbmBadge }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            {{ $tkbmOut }}
                            @if($tkbmBadge && $tkbmOut != '-')
                                <div style="font-size: 5.5px; color: #b45309; font-weight: bold;">{{ $tkbmBadge }}</div>
                            @endif
                        </td>

                        <!-- Catatan (Flexible Wrapping) -->
                        <td style="line-height: 1.2;">{{ $notes ?: '-' }}</td>

                        <!-- Status Asuransi -->
                        <td align="center" style="text-align: center;">
                            @if($ord->has_asuransi)
                                <span class="badge badge-done">&#10003; AKTIF</span>
                            @else
                                <span class="badge badge-pending">✗ TIDAK</span>
                            @endif
                        </td>

                        <!-- Status Invoice -->
                        <td style="text-align: center;">
                            @if($isInvoiced)
                                <span class="badge badge-done">✓ Terbit</span>
                                @if($invNumbers)
                                    <div style="font-size: 6px; color: #1e40af; margin-top: 1px;">{{ $invNumbers }}</div>
                                @endif
                            @else
                                <span class="badge badge-pending">✗ Belum</span>
                            @endif
                        </td>

                        <!-- Status PNBP -->
                        <td style="text-align: center;">
                            @if(strcasecmp($ord->source, 'Koperasi') === 0)
                                <span style="color: #94a3b8;">-</span>
                            @elseif($isPnbp)
                                <span class="badge badge-done">✓ Selesai</span>
                                @if($pnbpNum)
                                    <div style="font-size: 6px; color: #15803d; margin-top: 1px;">{{ $pnbpNum }}</div>
                                @endif
                            @else
                                <span class="badge badge-pending">✗ Belum</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="17" class="no-data">Tidak ada order kontainer yang berstatus Done pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. REKAPITULASI ORDER CARGO (DONE) -->
    <div class="section-title" style="margin-top: 14px;">
        2. Rekapitulasi Order Cargo (Muatan Bebas / General Cargo)
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="3" style="width: 2.5%;">No</th>
                <th rowspan="3" style="width: 7%;">No Order</th>
                <th rowspan="3" style="width: 9%;">Nama PT</th>
                <th rowspan="3" style="width: 7%;">Jenis Barang</th>
                <th rowspan="3" style="width: 5%;">Jml</th>
                <th rowspan="3" style="width: 5%;">Tonase</th>
                <th rowspan="3" style="width: 6%;">No BL</th>
                <th rowspan="3" style="width: 6.5%;">Vessel</th>
                <th rowspan="3" style="width: 5%;">Voyage</th>
                <th rowspan="3" style="width: 6%;">Surat Jalan</th>
                <th rowspan="3" style="width: 5%;">No BP</th>
                <th colspan="4" style="text-align: center;">Progress Waktu Lapangan</th>
                <th rowspan="3" style="width: 9.5%;">Catatan</th>
                <th rowspan="3" style="width: 6.5%;">Asuransi</th>
                <th rowspan="3" style="width: 6.5%;">Invoice</th>
                <th rowspan="3" style="width: 5.5%;">PNBP</th>
            </tr>
            <tr>
                <th colspan="2" style="width: 7%;">Waktu IN</th>
                <th colspan="2" style="width: 7%;">Waktu OUT / Selesai</th>
            </tr>
            <tr>
                <th colspan="2">Tgl & Jam IN</th>
                <th colspan="2">Tgl & Jam OUT</th>
            </tr>
        </thead>
        <tbody>
            @php $gRow = 1; @endphp
            @forelse($cargoOrders as $ord)
                @php
                    $inTimes = $ord->subTasks->pluck('in_time')->filter();
                    $outTimes = $ord->subTasks->pluck('out_time')->filter();
                    $doneTimes = $ord->subTasks->pluck('done_time')->filter();

                    $earliestIn = $inTimes->isNotEmpty() ? \Carbon\Carbon::parse($inTimes->min())->format('d/m/y H:i') : '-';
                    $latestOut = $outTimes->isNotEmpty() ? \Carbon\Carbon::parse($outTimes->max())->format('d/m/y H:i') : ($doneTimes->isNotEmpty() ? \Carbon\Carbon::parse($doneTimes->max())->format('d/m/y H:i') : '-');

                    $notesCargo = $ord->subTasks->pluck('in_note')->merge($ord->subTasks->pluck('out_note'))->merge($ord->subTasks->pluck('done_note'))->filter()->unique()->implode('; ');
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $gRow++ }}</td>
                    <td><strong>{{ $ord->order_number }}</strong></td>
                    <td>{{ $ord->nama_pt }}</td>
                    <td>{{ $ord->jenis_barang ?: 'General Cargo' }}</td>
                    <td>{{ $ord->jumlah_barang ?: '-' }}</td>
                    <td>{{ $ord->jumlah_tonase ? str_replace('.', ',', (float)$ord->jumlah_tonase) . ' T' : '-' }}</td>
                    <td>{{ $ord->nomor_bl ?: '-' }}</td>
                    <td>{{ $ord->vessel ?: '-' }}</td>
                    <td>{{ $ord->voyage ?: '-' }}</td>
                    <td>{{ $ord->no_surat_jalan ?: '-' }}</td>
                    <td>{{ $ord->no_bp ?: '-' }}</td>
                    
                    <!-- Waktu IN & OUT Cargo -->
                    <td colspan="2" style="text-align: center;">{{ $earliestIn }}</td>
                    <td colspan="2" style="text-align: center;">{{ $latestOut }}</td>

                    <!-- Catatan (Flexible Wrapping) -->
                    <td style="line-height: 1.2;">{{ $notesCargo ?: ($ord->pnbp_note ?: '-') }}</td>

                    <!-- Status Asuransi -->
                    <td align="center" style="text-align: center;">
                        @if($ord->has_asuransi)
                            <span class="badge badge-done">&#10003; AKTIF</span>
                        @else
                            <span class="badge badge-pending">✗ TIDAK</span>
                        @endif
                    </td>

                    <!-- Invoice -->
                    <td style="text-align: center;">
                        @if($ord->is_invoiced)
                            <span class="badge badge-done">✓ Terbit</span>
                            @if($ord->invoice_number)
                                <div style="font-size: 6px; color: #1e40af; margin-top: 1px;">{{ $ord->invoice_number }}</div>
                            @endif
                        @else
                            <span class="badge badge-pending">✗ Belum</span>
                        @endif
                    </td>

                    <!-- PNBP -->
                    <td style="text-align: center;">
                        @if(strcasecmp($ord->source, 'Koperasi') === 0)
                            <span style="color: #94a3b8;">-</span>
                        @elseif($ord->is_pnbp)
                            <span class="badge badge-done">✓ Selesai</span>
                            @if($ord->pnbp_number)
                                <div style="font-size: 6px; color: #15803d; margin-top: 1px;">{{ $ord->pnbp_number }}</div>
                            @endif
                        @else
                            <span class="badge badge-pending">✗ Belum</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" class="no-data">Tidak ada data order muatan cargo yang berstatus Done pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan Rekapitulasi Order Selesai &bull; Dicetak Otomatis oleh Sistem PT. Bintang Kepri Jaya pada {{ $tanggalCetak }}
    </div>
</body>
</html>
