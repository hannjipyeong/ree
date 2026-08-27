<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Permohonan - {{ $nomorSurat }}</title>
    <style>
        @page {
            margin: 15mm 18mm 15mm 18mm;
            size: a4 portrait;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10.5pt;
            line-height: 1.4;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Gambar */
        .kop-image-container {
            width: 100%;
            text-align: center;
            margin-bottom: 12px;
        }

        .kop-image {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Header Info Surat */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10pt;
        }

        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .info-label {
            width: 80px;
            font-weight: normal;
        }

        .info-separator {
            width: 15px;
            text-align: center;
        }

        .info-value {
            font-weight: normal;
        }

        .info-date {
            text-align: right;
            vertical-align: top;
            font-size: 10pt;
        }

        /* Tujuan Surat */
        .recipient-section {
            margin-bottom: 14px;
            font-size: 10pt;
            line-height: 1.4;
        }

        /* Isi Surat */
        .content-paragraph {
            text-align: justify;
            margin-bottom: 12px;
            font-size: 10pt;
            line-height: 1.45;
        }

        /* Tabel Rincian Data */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10pt;
        }

        .detail-table td {
            padding: 3.5px 4px;
            vertical-align: top;
        }

        .detail-label {
            width: 170px;
            color: #1a1a1a;
        }

        .detail-separator {
            width: 15px;
            text-align: center;
        }

        .detail-value {
            font-weight: normal;
            color: #111827;
        }

        /* Tanda Tangan Statik */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .signature-table td {
            vertical-align: top;
        }

        .signature-image {
            width: 170px;
            height: auto;
            display: block;
        }

        .page-break {
            page-break-before: always;
        }

        .manifest-container {
            width: 100%;
            text-align: center;
            padding-top: 5px;
        }

        .manifest-img {
            max-width: 100%;
            max-height: 265mm;
            width: auto;
            height: auto;
            display: inline-block;
        }
    </style>
</head>
<body>

    <!-- ==================== HALAMAN 1 ==================== -->

    <!-- KOP SURAT GAMBAR -->
    <div class="kop-image-container">
        @if($kopBase64)
            <img src="{{ $kopBase64 }}" class="kop-image" alt="Kop Surat PT. Bintang Kepri Jaya">
        @endif
    </div>

    <!-- HEADER SURAT (Nomor, Lampiran, Perihal, Tanggal Export) -->
    <table class="info-table">
        <tr>
            <td style="width: 58%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="info-label">Nomor</td>
                        <td class="info-separator">:</td>
                        <td class="info-value"><strong>{{ $nomorSurat }}</strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Lampiran</td>
                        <td class="info-separator">:</td>
                        <td class="info-value">{{ $lampiran }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Perihal</td>
                        <td class="info-separator">:</td>
                        <td class="info-value"><strong>{{ $perihal }}</strong></td>
                    </tr>
                </table>
            </td>
            <td class="info-date" style="width: 42%;">
                Batam, {{ $tanggalExport }}
            </td>
        </tr>
    </table>

    @if($isCargo)
        <!-- ================= FORMAT KHUSUS CARGO ================= -->
        <!-- TUJUAN SURAT (KEPADA YTH) -->
        <div class="recipient-section">
            Kepada Yth :<br>
            <strong>Koordinator</strong><br>
            Terminal Umum {{ $order->wilayah ? $order->wilayah : 'Batu Ampar' }}<br>
            Di-<br>
            <u>{{ $order->wilayah ?: 'Batu Ampar' }}</u>
        </div>

        <!-- PEMBUKA SURAT -->
        <div class="content-paragraph">
            Dengan Hormat,<br><br>
            Bersama ini kami beritahukan permohonan {{ strtolower($order->jenis_kegiatan ?: 'penumpukan') }} atas barang yang kami tumpuk di Pelabuhan {{ $order->wilayah ?: 'Batu Ampar' }}.<br>
            Adapun jenis barang yang kami tumpuk adalah
        </div>

        <!-- TABEL RINCIAN DATA CARGO -->
        <table class="detail-table">
            <tr>
                <td class="detail-label">Tanggal Penumpukan</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ $tanggalPenumpukan }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Jenis Container</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ $order->jenis_barang ?: 'General Cargo' }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Pemilik Barang</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ $order->nama_pt }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Jumlah</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ $order->jumlah_tonase ? str_replace('.', ',', (float)$order->jumlah_tonase) . ' Ton' : '-' }}</strong></td>
            </tr>
        </table>

        <!-- PENUTUP SURAT -->
        <div class="content-paragraph" style="margin-top: 14px;">
            Demikianlah permohonan {{ ucfirst(strtolower($order->jenis_kegiatan ?: 'Penumpukan')) }} ini kami buat atas perhatiannya kami ucapkan terima kasih.
        </div>

        <!-- TANDA TANGAN -->
        <table class="signature-table">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; padding-left: 20px;">
                    <div>Hormat kami</div>
                    <div style="font-weight: bold; margin-bottom: 2px;">PT. BINTANG KEPRI JAYA</div>
                    @if($ttdBase64)
                        <img src="{{ $ttdBase64 }}" class="signature-image" alt="Tanda Tangan & Cap PT. Bintang Kepri Jaya">
                    @else
                        <div style="height: 60px;"></div>
                    @endif
                    <div style="font-weight: bold; text-decoration: underline; margin-top: 2px;">Nandi Pinto</div>
                    <div style="font-size: 9.5pt; color: #374151;">Operational</div>
                </td>
            </tr>
        </table>

        <!-- ==================== HALAMAN 2: MANIFEST CARGO ==================== -->
        <div class="page-break"></div>
        <div class="manifest-container">
            @if($manifestBase64)
                <img src="{{ $manifestBase64 }}" class="manifest-img" alt="Manifest / Dokumen Cargo">
            @else
                <div style="border: 2px dashed #9ca3af; padding: 40px; text-align: center; color: #6b7280; margin-top: 80px; border-radius: 8px;">
                    <h3 style="font-size: 13pt; margin-bottom: 8px; color: #374151;">DOKUMEN MANIFEST CARGO</h3>
                    <p style="font-size: 10pt;">Lampiran dokumen manifest cargo belum diunggah atau tidak ditemukan.</p>
                </div>
            @endif
        </div>

    @else
        <!-- ================= FORMAT STANDAR CONTAINER ================= -->
        <!-- TUJUAN SURAT (KEPADA YTH) -->
        <div class="recipient-section">
            Kepada Yth.<br>
            <strong>Pengelola / Otoritas Lapangan & Terminal</strong><br>
            {{ $order->wilayah ? 'Wilayah ' . $order->wilayah : 'Batam' }} — {{ $order->lokasi_fasilitas ?: 'TPFT' }}<br>
            Di Tempat
        </div>

        <!-- PEMBUKA SURAT -->
        @php
            $entityName = strcasecmp($order->source, 'Koperasi') === 0 ? 'Koperasi TKBM PT. Bintang Kepri Jaya' : 'PT. Bintang Kepri Jaya';
        @endphp
        <div class="content-paragraph">
            Dengan hormat,<br>
            Sehubungan dengan kelancaran kegiatan operasional pengiriman dan logistik muatan, bersama surat ini kami dari <strong>{{ $entityName }}</strong> mengajukan permohonan <strong>{{ strtolower($order->jenis_kegiatan ?: 'penumpukan') }}</strong> dengan rincian data sebagai berikut:
        </div>

        <!-- TABEL RINCIAN PERMOHONAN CONTAINER -->
        <table class="detail-table">
            <tr>
                <td class="detail-label">Pemilik Barang</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ $order->nama_pt }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Nama PBM</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ $order->nama_pbm ?: '-' }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Jenis Kegiatan</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ strtoupper($order->jenis_kegiatan ?: 'PENUMPUKAN') }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Tanggal Penumpukan</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ $tanggalPenumpukan }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Lokasi / Fasilitas</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ $order->wilayah }} — {{ $order->lokasi_fasilitas }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Jenis Container</td>
                <td class="detail-separator">:</td>
                <td class="detail-value"><strong>{{ $jenisContainer }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Jumlah / Unit</td>
                <td class="detail-separator">:</td>
                <td class="detail-value">
                    <div><strong>{{ $order->containers->count() }} Container {{ $jenisContainer }}</strong></div>
                    @php
                        $containers = $order->containers->filter(fn($c) => !empty($c->container_number));
                    @endphp
                    @if($containers->isNotEmpty())
                        <div style="margin-top: 3px; font-weight: normal; font-size: 9.5pt; color: #111827; line-height: 1.5;">
                            @foreach($containers as $c)
                                <span style="white-space: nowrap;">-&nbsp;&nbsp;{{ $c->container_number }}</span>@if(!$loop->last)&nbsp;&nbsp;&nbsp;&nbsp;@endif
                            @endforeach
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- PENUTUP SURAT -->
        <div class="content-paragraph" style="margin-top: 14px;">
            Demikian surat permohonan ini kami sampaikan. Atas perhatian, bantuan, dan kerja sama yang diberikan, kami ucapkan terima kasih.
        </div>

        <!-- TANDA TANGAN GAMBAR -->
        <table class="signature-table">
            <tr>
                <td style="width: 55%;">
                    <!-- Ruang kosong di kiri -->
                </td>
                <td style="width: 45%; text-align: center;">
                    <div style="margin-bottom: 4px;">Batam, {{ $tanggalExport }}</div>
                    @if($ttdBase64)
                        <img src="{{ $ttdBase64 }}" class="signature-image" style="margin: 0 auto;" alt="Tanda Tangan & Cap PT. Bintang Kepri Jaya">
                    @else
                        <div style="font-weight: bold; margin-top: 4px;">Hormat kami,<br>{{ strtoupper($entityName) }}</div>
                        <div style="height: 60px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">Nandi Pinto</div>
                        <div style="font-size: 9pt; color: #4b5563;">Operational</div>
                    @endif
                </td>
            </tr>
        </table>
    @endif

</body>
</html>
