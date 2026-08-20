<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Order Selesai (Done) - BKJ Ops</title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .meta-table {
            width: 100%;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            font-size: 8.5px;
        }
        .meta-value {
            color: #0f172a;
            font-weight: 600;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid #0f172a;
            text-align: left;
        }
        table.data-table td {
            padding: 5px;
            border: 1px solid #cbd5e1;
            font-size: 8px;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-done {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .badge-invoiced {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .badge-not-invoiced {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .footer {
            margin-top: 15px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 8px;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="title">PT. BERKAH KARYA JASATAMA</div>
                <div class="subtitle">Laporan Monitoring & Rekapitulasi Order Selesai (Status: DONE)</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div style="font-size: 8.5px; color: #475569;">Dicetak pada: <strong>{{ $tanggalCetak }}</strong></div>
                <div style="font-size: 8.5px; color: #475569;">Oleh: <strong>{{ $adminUser }}</strong></div>
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
                    <span class="meta-label">Filter Modul / Source:</span><br>
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

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No.</th>
                <th style="width: 10%;">No. Order</th>
                <th style="width: 7%;">Tgl Order</th>
                <th style="width: 6%;">Source</th>
                <th style="width: 15%;">Customer & PBM</th>
                <th style="width: 13%;">Wilayah & Lokasi</th>
                <th style="width: 11%;">Muatan / Payload</th>
                <th style="width: 14%;">Layanan & Tiket SubTask</th>
                <th style="width: 8%; text-align: center;">Status Lapangan</th>
                <th style="width: 12%; text-align: center;">Status Invoice</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $ord)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $ord->order_number }}</strong>
                    </td>
                    <td>{{ $ord->tanggal_order ? $ord->tanggal_order->format('d/m/Y') : '-' }}</td>
                    <td>{{ $ord->source }}</td>
                    <td>
                        <strong>{{ $ord->nama_pt }}</strong><br>
                        <span style="color: #64748b; font-size: 7.5px;">PBM: {{ $ord->nama_pbm ?: '-' }}</span>
                    </td>
                    <td>
                        <strong>{{ $ord->wilayah }}</strong><br>
                        <span style="color: #64748b; font-size: 7.5px;">{{ $ord->lokasi_fasilitas }} ({{ $ord->jenis_kegiatan }})</span>
                    </td>
                    <td>
                        @if(strtolower($ord->payload_type) === 'cargo')
                            <strong>Cargo:</strong> {{ $ord->jenis_barang ?: 'General' }}
                            @if($ord->jumlah_tonase) ({{ str_replace('.', ',', (float)$ord->jumlah_tonase) }} Ton) @endif
                        @else
                            <strong>Container ({{ $ord->containers->count() }}):</strong><br>
                            <span style="color: #475569; font-size: 7.5px;">
                                {{ $ord->containers->pluck('container_number')->filter()->implode(', ') ?: 'Tanpa nomor' }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @foreach($ord->subTasks as $st)
                            <div style="margin-bottom: 2px;">
                                <strong>{{ $st->service_type }}</strong>: {{ $st->status }}
                                @if($st->supir) ({{ $st->supir->name }}) @endif
                            </div>
                        @endforeach
                        @if($ord->has_asuransi)
                            <div style="color: #047857; font-size: 7.5px;">+ Asuransi Cargo @if($ord->asuransi_value) (Rp {{ number_format($ord->asuransi_value, 0, ',', '.') }}) @endif</div>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-done">{{ $ord->status }}</span>
                    </td>
                    <td style="text-align: center;">
                        @if($ord->is_invoiced)
                            <span class="badge badge-invoiced">Sudah Terbit</span>
                            @if($ord->invoice_number)
                                <div style="font-size: 7px; color: #1e40af; margin-top: 1px;">No: {{ $ord->invoice_number }}</div>
                            @endif
                            @if($ord->invoiced_at)
                                <div style="font-size: 6.5px; color: #64748b;">{{ $ord->invoiced_at->format('d/m/Y') }}</div>
                            @endif
                        @else
                            <span class="badge badge-not-invoiced">Belum Keluar</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Tidak ada data order berstatus Done pada rentang tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen Laporan Operasional BKJ Ops &bull; Dicetak secara otomatis dari Sistem BKJ Platform
    </div>

</body>
</html>
