<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 10px; margin-bottom: 14px; }
        .header h1 { font-size: 16px; font-weight: bold; color: #1e3a8a; }
        .header .meta { font-size: 9px; color: #64748b; margin-top: 4px; }
        .filter-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
        .chip { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 9px; font-weight: bold; border: 1px solid #cbd5e1; background: #f1f5f9; color: #475569; }
        .chip.status { background: #dbeafe; border-color: #93c5fd; color: #1d4ed8; }
        .chip.layanan { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }
        .chip.date { background: #fef9c3; border-color: #fde047; color: #713f12; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead { background: #1e3a8a; color: white; }
        thead th { padding: 7px 8px; font-size: 9px; font-weight: bold; text-align: left; letter-spacing: 0.04em; text-transform: uppercase; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 6px 8px; vertical-align: top; font-size: 9px; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .badge-in { background: #dbeafe; color: #1d4ed8; }
        .badge-out { background: #fef3c7; color: #b45309; }
        .badge-done { background: #d1fae5; color: #065f46; }
        .badge-masuk { background: #f1f5f9; color: #475569; }
        .badge-invoiced { background: #d1fae5; color: #065f46; }
        .badge-not-invoiced { background: #fee2e2; color: #b91c1c; }
        .footer { margin-top: 20px; font-size: 8px; color: #94a3b8; text-align: right; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        .no-data { text-align: center; padding: 20px; color: #94a3b8; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Dashboard — Berkah Karya Jasatama</h1>
        <div class="meta">
            Dicetak oleh: {{ $adminUser }} &nbsp;|&nbsp; {{ $tanggalCetak }} &nbsp;|&nbsp; Periode: {{ $periodeText }}
        </div>
    </div>

    <div class="filter-chips">
        <span class="chip status">Status: {{ $activeStatus ?? 'Semua Status' }}</span>
        <span class="chip layanan">Layanan: {{ $activeLayanan ?? 'Semua Layanan' }}</span>
        <span class="chip date">Periode: {{ $periodeText }}</span>
        @if(!empty($search))
            <span class="chip">Pencarian: "{{ $search }}"</span>
        @endif
        <span class="chip">Total: {{ $orders->count() }} order</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:12%">No. Order</th>
                <th style="width:15%">Nama PT</th>
                <th style="width:12%">No. Container</th>
                <th style="width:10%">Ukuran/Tipe</th>
                <th style="width:10%">Layanan</th>
                <th style="width:8%">Status</th>
                <th style="width:10%">Waktu</th>
                <th style="width:10%">Catatan</th>
                <th style="width:10%">Invoice</th>
            </tr>
        </thead>
        <tbody>
            @php $rowNo = 1; @endphp
            @forelse($orders as $ord)
                @foreach($ord->containers as $c)
                    @foreach($c->progresses as $prog)
                        @php
                            $st = $prog->subTask;
                            if (!$st) continue;
                            if ($activeStatus && $activeStatus != 'Semua Status' && $st->status != $activeStatus) continue;
                            if ($activeLayanan && $activeLayanan != 'Semua Layanan' && $st->service_type != $activeLayanan) continue;

                            $waktu = '-';
                            $catatan = '-';
                            if ($st->status == 'In') {
                                $waktu = $prog->in_time ? \Carbon\Carbon::parse($prog->in_time)->format('d/m/Y H:i') : '-';
                                $catatan = $prog->in_note ?: '-';
                            } elseif ($st->status == 'Out') {
                                $waktu = $prog->out_time ? \Carbon\Carbon::parse($prog->out_time)->format('d/m/Y H:i') : '-';
                                $catatan = $prog->out_note ?: '-';
                            } elseif ($st->status == 'Done') {
                                $waktu = $prog->done_time ? \Carbon\Carbon::parse($prog->done_time)->format('d/m/Y H:i') : '-';
                                $catatan = $prog->done_note ?: '-';
                            }
                        @endphp
                        <tr>
                            <td>{{ $rowNo++ }}</td>
                            <td><strong>{{ $ord->order_number }}</strong></td>
                            <td>{{ $ord->nama_pt }}</td>
                            <td>{{ $c->container_number ?: 'No-ID' }}</td>
                            <td>{{ $c->container_size }} / {{ $c->container_type }}</td>
                            <td>{{ $st->service_type }}</td>
                            <td>
                                <span class="badge
                                    {{ $st->status == 'In' ? 'badge-in' : '' }}
                                    {{ $st->status == 'Out' ? 'badge-out' : '' }}
                                    {{ $st->status == 'Done' ? 'badge-done' : '' }}
                                    {{ $st->status == 'Masuk' ? 'badge-masuk' : '' }}">
                                    {{ $st->status }}
                                </span>
                            </td>
                            <td>{{ $waktu }}</td>
                            <td>{{ Str::limit($catatan, 30) }}</td>
                            <td>
                                @if($prog->is_invoiced)
                                    <span class="badge badge-invoiced">✓ Terbit</span>
                                    @if($prog->invoice_number)
                                        <br><small style="color:#64748b">{{ $prog->invoice_number }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-not-invoiced">✗ Belum</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @empty
                <tr>
                    <td colspan="10" class="no-data">Tidak ada data untuk filter yang dipilih.</td>
                </tr>
            @endforelse
            @if($rowNo == 1 && $orders->isNotEmpty())
                <tr>
                    <td colspan="10" class="no-data">Tidak ada kontainer yang sesuai dengan filter spesifik (Layanan/Status).</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dashboard Export PDF — {{ $tanggalCetak }} — Sistem Manajemen Operasional BKJ
    </div>
</body>
</html>
