@extends('layouts.app')

@section('title', 'Daftar Kontainer — Request ' . $order->order_number)
@section('page_heading', 'Order Request: ' . $order->order_number)

@section('content')
<div class="space-y-8">

    <!-- Top Action & Navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route('requests.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Request
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <span class="px-3 py-1 bg-purple-50 text-purple-700 font-semibold rounded-full text-xs border border-purple-200">
                Source: {{ $order->source }}
            </span>
            
            <a href="{{ route('requests.exportPdf', $order->id) }}" target="_blank" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-rose-600/20 transition">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Cetak / Ekspor Surat PDF</span>
            </a>

            <button type="button" onclick="openEditServicesModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-blue-600/20 transition">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Layanan Order & Surat</span>
            </button>
        </div>
    </div>

    <!-- Overview Card & Invoice Banner -->
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <div class="text-xs text-slate-400 font-bold uppercase">Customer / PT</div>
                <div class="text-lg font-bold text-slate-800 mt-1">{{ $order->nama_pt }}</div>
                <div class="text-xs text-slate-500 mt-0.5">PBM: {{ $order->nama_pbm }} | Telp: {{ $order->no_telp }}</div>
            </div>

            <div>
                <div class="text-xs text-slate-400 font-bold uppercase">Wilayah & Fasilitas</div>
                <div class="text-base font-bold text-slate-800 mt-1">{{ $order->wilayah }} — {{ $order->lokasi_fasilitas }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Jenis Kegiatan: {{ $order->jenis_kegiatan }}</div>
            </div>

            <div>
                <div class="text-xs text-slate-400 font-bold uppercase">Tanggal Order & Payload</div>
                <div class="text-base font-bold text-slate-800 mt-1">{{ $order->tanggal_order ? $order->tanggal_order->format('d F Y') : '-' }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Payload: {{ $order->payload_type }}</div>
            </div>

            <div>
                <div class="text-xs text-slate-400 font-bold uppercase">Layanan & Asuransi (Order)</div>
                <div class="text-sm font-bold text-slate-800 mt-1">
                    @if($order->tkbm_option)
                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 font-semibold rounded border border-amber-200 text-xs inline-block">
                            <i class="fa-solid fa-users-gear me-1"></i> TKBM: {{ $order->tkbm_option }}
                        </span>
                    @else
                        <span class="text-slate-400 font-normal text-xs">Tanpa TKBM Khusus</span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 mt-1.5">
                    @if($order->has_asuransi)
                        <span class="text-emerald-600 font-semibold flex items-center gap-1 text-xs">
                            <i class="fa-solid fa-shield-halved"></i> Asuransi Aktif
                        </span>
                    @else
                        <span class="text-slate-400 font-normal text-xs">Tanpa Asuransi</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Removed Invoice Toggle Card as it is now managed per container in Dashboard -->
    </div>

    @if(str_contains(strtolower($order->payload_type), 'cargo') || $order->containers->isEmpty())
        <!-- LEVEL 2: Detail Payload Cargo & Status PNBP -->
        <div class="bg-white rounded-2xl border border-amber-200/80 shadow-sm p-6 space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-amber-100 pb-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-3 py-1 bg-amber-100 text-amber-800 border border-amber-300 font-bold rounded-xl text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-boxes-packing"></i> PAYLOAD CARGO
                        </span>
                        @if($order->is_pnbp)
                            <button type="button" onclick="openPnbpCargoModal()" class="px-3 py-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300 font-extrabold rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm" title="Klik untuk kelola PNBP">
                                <i class="fa-solid fa-file-invoice-dollar text-[11px]"></i> PNBP: Selesai ({{ $order->pnbp_number ?: 'Terbit' }})
                            </button>
                        @else
                            <button type="button" onclick="openPnbpCargoModal()" class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm" title="Klik untuk konfirmasi PNBP">
                                <i class="fa-solid fa-clock text-[10px]"></i> PNBP: Belum Selesai
                            </button>
                        @endif
                    </div>
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <i class="fa-solid fa-boxes-packing text-amber-600"></i>
                        Rincian Muatan Cargo & Dokumen Manifest
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Order ini berjenis Cargo (Muatan Bebas / General Cargo) tanpa nomor kontainer baku.</p>
                </div>
                
                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <!-- PNBP Action Button for Cargo -->
                    <button type="button" onclick="openPnbpCargoModal()" class="px-4 py-2 {{ $order->is_pnbp ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30' }} text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-lg transition">
                        <i class="fa-solid fa-receipt"></i>
                        <span>{{ $order->is_pnbp ? 'PNBP: Selesai (Edit)' : 'Check / Konfirmasi PNBP Cargo' }}</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Barang</div>
                    <div class="text-sm font-black text-slate-800">{{ $order->jenis_barang ?: 'General Cargo' }}</div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jumlah Barang</div>
                    <div class="text-sm font-black text-slate-800">{{ $order->jumlah_barang ?: '-' }}</div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jumlah Tonase</div>
                    <div class="text-sm font-black text-slate-800">{{ $order->jumlah_tonase ? str_replace('.', ',', (float)$order->jumlah_tonase) . ' Ton' : '-' }}</div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor BL</div>
                    <div class="text-sm font-black text-slate-800">{{ $order->nomor_bl ?: '-' }}</div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Vessel (Nama Kapal)</div>
                    <div class="text-sm font-black text-slate-800">{{ $order->vessel ?: '-' }}</div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Voyage (Keberangkatan)</div>
                    <div class="text-sm font-black text-slate-800">{{ $order->voyage ?: '-' }}</div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">No. Surat Jalan</div>
                    <div class="text-sm font-black text-slate-800">{{ $order->no_surat_jalan ?: '-' }}</div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">No. BP (Plat Nomor)</div>
                    <div class="text-sm font-black text-slate-800">{{ $order->no_bp ?: '-' }}</div>
                </div>

                @if($order->nomor_container_cargo)
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 col-span-2 md:col-span-4">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">No. Container Cargo (Ref)</div>
                        <div class="text-sm font-black text-slate-800">{{ $order->nomor_container_cargo }}</div>
                    </div>
                @endif
            </div>

            <!-- PNBP Summary Card for Cargo -->
            <div class="p-4 bg-slate-900 text-white rounded-2xl border border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $order->is_pnbp ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-400/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white flex items-center gap-2">
                            <span>Status PNBP Muatan Cargo:</span>
                            @if($order->is_pnbp)
                                <span class="text-emerald-400 font-extrabold flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-[11px]"></i> Sudah Selesai / Terbit
                                </span>
                                @if($order->pnbp_number)
                                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-mono border border-emerald-400/30">{{ $order->pnbp_number }}</span>
                                @endif
                            @else
                                <span class="text-rose-400 font-extrabold flex items-center gap-1">
                                    <i class="fa-solid fa-circle-xmark text-[11px]"></i> Belum Selesai
                                </span>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-300 mt-0.5">
                            @if($order->pnbp_note)
                                <span class="text-slate-400 font-medium">Notes Submission:</span> <span class="text-white italic">"{{ $order->pnbp_note }}"</span>
                            @else
                                <span class="text-slate-500 italic">Belum ada catatan submission PNBP</span>
                            @endif
                            @if($order->pnbp_completed_at)
                                <span class="text-slate-400 ml-2 text-[10px]">(Diverifikasi: {{ $order->pnbp_completed_at->format('d/m/Y H:i') }})</span>
                            @endif
                        </div>
                    </div>
                </div>
                <button type="button" onclick="openPnbpCargoModal()" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold border border-white/20 transition flex items-center gap-2 self-end sm:self-auto shadow-sm">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Kelola PNBP & Notes Cargo</span>
                </button>
            </div>

            <!-- Lampiran Dokumen Manifest Cargo -->
            <div class="p-4 bg-amber-50/60 rounded-xl border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-800">Lampiran Dokumen Manifest Cargo</div>
                        <div class="text-xs text-slate-500">
                            @if(is_array($order->cargo_file_path) && count($order->cargo_file_path) > 0)
                                Terlampir {{ count($order->cargo_file_path) }} berkas (Otomatis menjadi lampiran Surat Permohonan)
                            @elseif(is_string($order->cargo_file_path))
                                Terlampir: {{ basename($order->cargo_file_path) }} (Otomatis menjadi Hal. 2 Surat Permohonan)
                            @else
                                Belum ada berkas manifest yang diunggah
                            @endif
                        </div>
                    </div>
                </div>

                @if(is_array($order->cargo_file_path) && count($order->cargo_file_path) > 0)
                    <div class="flex items-center gap-2 flex-wrap">
                        @foreach($order->cargo_file_path as $idx => $path)
                        <a href="{{ asset($path) }}" target="_blank" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>Buka Berkas {{ count($order->cargo_file_path) > 1 ? '#' . ($idx + 1) : '' }}</span>
                        </a>
                        @endforeach
                    </div>
                @elseif(is_string($order->cargo_file_path))
                    <div class="flex items-center gap-2">
                        <a href="{{ asset($order->cargo_file_path) }}" target="_blank" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>Buka Berkas Manifest</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Monitoring Tiket Layanan & Progress Lapangan Cargo (IN / OUT Process) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <i class="fa-solid fa-truck-ramp-box text-blue-600"></i>
                        Monitoring Tiket Layanan & Progress Lapangan Cargo ({{ $order->subTasks->count() }})
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau aktivitas proses IN, OUT, bukti foto, dan catatan pelaksana lapangan untuk muatan Cargo.</p>
                </div>
            </div>

            <div class="space-y-6">
                @forelse($order->subTasks as $st)
                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                        <div id="subtask-display-{{ $st->id }}" class="space-y-4">
                            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-extrabold text-slate-800 text-base">{{ $st->task_number }}</span>
                                        <span class="px-2.5 py-0.5 rounded text-xs font-extrabold uppercase border
                                            {{ $st->service_type == 'Railing' ? 'bg-purple-100 text-purple-800 border-purple-200' : '' }}
                                            {{ $st->service_type == 'LOLO' ? 'bg-sky-100 text-sky-800 border-sky-200' : '' }}
                                            {{ $st->service_type == 'Storage' ? 'bg-amber-100 text-amber-800 border-amber-200' : '' }}
                                            {{ $st->service_type == 'TKBM' ? 'bg-teal-100 text-teal-800 border-teal-200' : '' }}
                                            {{ !in_array($st->service_type, ['Railing', 'LOLO', 'Storage', 'TKBM']) ? 'bg-slate-800 text-white' : '' }}">
                                            {{ $st->service_type }}
                                        </span>
                                    </div>
                                    @if($st->service_type == 'TKBM')
                                        @php $tkbmOpt = $order->tkbm_option ?: 'Man Power'; @endphp
                                        <div class="mt-1.5">
                                            @if(str_contains(strtolower($tkbmOpt), 'forklift'))
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                                    <i class="fa-solid fa-forklift text-[10px]"></i> Man Power + Forklift
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                                                    <i class="fa-solid fa-people-carry-box text-[10px]"></i> Man Power
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="text-xs text-slate-500 mt-1">
                                        Pelaksana Lapangan / Driver: 
                                        <strong class="text-slate-700">{{ $st->supir ? $st->supir->name : 'Belum ditugaskan' }}</strong>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div>
                                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase shadow-sm
                                        {{ $st->status == 'Masuk' ? 'bg-slate-200 text-slate-800' : '' }}
                                        {{ $st->status == 'IN' || $st->status == 'In' ? 'bg-blue-600 text-white' : '' }}
                                        {{ $st->status == 'OUT' || $st->status == 'Out' ? 'bg-amber-500 text-white' : '' }}
                                        {{ $st->status == 'DONE' || $st->status == 'Done' ? 'bg-emerald-600 text-white' : '' }}">
                                        Status Lapangan: {{ $st->status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Notes & Photo Proofs per Cargo SubTask (Multi-Photo Gallery) -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3 border-t border-slate-200 text-xs">
                                <!-- Bukti IN -->
                                <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                                    <div class="font-bold text-blue-600 flex items-center justify-between">
                                        <span><i class="fa-solid fa-right-to-bracket me-1"></i> Foto IN</span>
                                        @if($st->in_time)
                                            <span class="text-[10px] text-slate-400 font-medium">
                                                <i class="fa-regular fa-clock me-0.5"></i>{{ $st->in_time->format('d M Y, H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                    @php $allInPhotos = $st->all_in_photos; @endphp
                                    @if(!empty($allInPhotos))
                                        <div class="grid grid-cols-3 gap-1.5 pt-1">
                                            @foreach($allInPhotos as $inPhoto)
                                                @php $inPhotoUrl = Str::startsWith($inPhoto, ['http://', 'https://']) ? $inPhoto : asset($inPhoto); @endphp
                                                <button type="button" onclick="openPhotoModal('{{ $inPhotoUrl }}', 'Foto IN Cargo')" class="aspect-square rounded-lg overflow-hidden border border-slate-200 hover:border-blue-400 transition cursor-pointer">
                                                    <img src="{{ $inPhotoUrl }}" alt="Foto IN" class="w-full h-full object-cover">
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-[11px] text-slate-400 italic py-2">Belum ada foto IN diunggah</div>
                                    @endif
                                    <div class="text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-[11px]">
                                        <strong class="text-slate-700 block text-[10px] uppercase font-bold text-slate-400">Catatan IN:</strong>
                                        {{ $st->in_note ?: '-' }}
                                    </div>
                                </div>

                                <!-- Bukti OUT -->
                                <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                                    <div class="font-bold text-amber-600 flex items-center justify-between">
                                        <span><i class="fa-solid fa-right-from-bracket me-1"></i> Foto OUT</span>
                                        @if($st->out_time)
                                            <span class="text-[10px] text-slate-400 font-medium">
                                                <i class="fa-regular fa-clock me-0.5"></i>{{ $st->out_time->format('d M Y, H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                    @php $allOutPhotos = $st->all_out_photos; @endphp
                                    @if(!empty($allOutPhotos))
                                        <div class="grid grid-cols-3 gap-1.5 pt-1">
                                            @foreach($allOutPhotos as $outPhoto)
                                                @php $outPhotoUrl = Str::startsWith($outPhoto, ['http://', 'https://']) ? $outPhoto : asset($outPhoto); @endphp
                                                <button type="button" onclick="openPhotoModal('{{ $outPhotoUrl }}', 'Foto OUT Cargo')" class="aspect-square rounded-lg overflow-hidden border border-slate-200 hover:border-amber-400 transition cursor-pointer">
                                                    <img src="{{ $outPhotoUrl }}" alt="Foto OUT" class="w-full h-full object-cover">
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-[11px] text-slate-400 italic py-2">Belum ada foto OUT diunggah</div>
                                    @endif
                                    <div class="text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-[11px]">
                                        <strong class="text-slate-700 block text-[10px] uppercase font-bold text-slate-400">Catatan OUT:</strong>
                                        {{ $st->out_note ?: '-' }}
                                    </div>
                                </div>

                                <!-- Bukti DONE -->
                                <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                                    <div class="font-bold text-emerald-600 flex items-center justify-between">
                                        <span><i class="fa-solid fa-circle-check me-1"></i> Foto DONE</span>
                                        @if($st->done_time)
                                            <span class="text-[10px] text-slate-400 font-medium">
                                                <i class="fa-regular fa-clock me-0.5"></i>{{ $st->done_time->format('d M Y, H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                    @php $allDonePhotos = $st->all_done_photos; @endphp
                                    @if(!empty($allDonePhotos))
                                        <div class="grid grid-cols-3 gap-1.5 pt-1">
                                            @foreach($allDonePhotos as $donePhoto)
                                                @php $donePhotoUrl = Str::startsWith($donePhoto, ['http://', 'https://']) ? $donePhoto : asset($donePhoto); @endphp
                                                <button type="button" onclick="openPhotoModal('{{ $donePhotoUrl }}', 'Foto DONE Cargo')" class="aspect-square rounded-lg overflow-hidden border border-slate-200 hover:border-emerald-400 transition cursor-pointer">
                                                    <img src="{{ $donePhotoUrl }}" alt="Foto DONE" class="w-full h-full object-cover">
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-[11px] text-slate-400 italic py-2">Belum ada foto DONE diunggah</div>
                                    @endif
                                    <div class="text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-[11px]">
                                        <strong class="text-slate-700 block text-[10px] uppercase font-bold text-slate-400">Catatan DONE:</strong>
                                        {{ $st->done_note ?: ($st->out_note ?: '-') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Update Status Form (Admin Action) for Cargo SubTask -->
                        <div class="pt-4 border-t border-slate-200">
                            <form id="statusForm-{{ $st->id }}" action="{{ route('subtasks.updateStatus', $st->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return submitStatusAjax(event, {{ $st->id }})" class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
                                @csrf
                                @method('PATCH')

                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Ubah Status</label>
                                        <select name="status" id="statusSelect-{{ $st->id }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600">
                                            <option value="Masuk" {{ $st->status == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                                            <option value="In" {{ in_array($st->status, ['In', 'IN']) ? 'selected' : '' }}>In</option>
                                            <option value="Out" {{ in_array($st->status, ['Out', 'OUT']) ? 'selected' : '' }}>Out</option>
                                            <option value="Done" {{ in_array($st->status, ['Done', 'DONE']) ? 'selected' : '' }}>Done</option>
                                            <option value="Pending" {{ $st->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Catatan Tambahan</label>
                                        <input type="text" name="note" placeholder="Tulis catatan..." class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Upload Foto Bukti</label>
                                        <input type="file" name="photos[]" multiple accept="image/*" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-xl text-[11px] text-slate-600 focus:outline-none file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    </div>
                                </div>

                                <div class="md:self-end">
                                    <button type="submit" id="submitBtn-{{ $st->id }}" class="w-full md:w-auto px-5 py-2 bg-slate-900 hover:bg-blue-600 text-white font-bold rounded-xl text-xs shadow transition flex items-center justify-center gap-2">
                                        <span class="btn-text">Simpan Status</span>
                                        <span class="btn-spinner hidden"><i class="fa-solid fa-spinner fa-spin"></i></span>
                                    </button>
                                </div>
                            </form>
                            <div id="statusMsg-{{ $st->id }}" class="hidden text-xs font-bold px-3 py-2 mt-2 rounded-xl flex items-center gap-2 transition-all duration-300"></div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 bg-slate-50 rounded-2xl border border-dashed border-slate-300 text-center text-slate-400 text-xs">
                        Belum ada tiket layanan untuk muatan cargo ini.
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    @if(!str_contains(strtolower($order->payload_type), 'cargo') || $order->containers->isNotEmpty())
        <!-- LEVEL 2: List Container (Vertical Full-Width List Layout) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-blue-600"></i>
                        Daftar Kontainer dalam Request ini ({{ $order->containers->count() }})
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Setiap kontainer dapat memiliki opsi TKBM & layanan tersendiri. Pilih kontainer di bawah untuk mengedit atau melihat detailnya.</p>
                </div>
                
                @php
                    $childOrder = \App\Models\Order::where('parent_order_id', $order->id)->first();
                    $hasTkbmOrChild = $order->subTasks->where('service_type', 'TKBM')->isNotEmpty() || $childOrder;
                @endphp
                @if(strtolower($order->source) == 'all in' && $hasTkbmOrChild && auth()->user()->role === 'admin' && in_array(auth()->user()->admin_source, ['ALL IN', null]))
                    <div>
                        @if($childOrder)
                            <a href="{{ route('requests.show', $childOrder->id) }}" class="px-4 py-2 bg-teal-50 text-teal-700 font-bold rounded-xl text-xs flex items-center gap-2 border border-teal-200 hover:bg-teal-100 transition shadow-sm" title="Order Koperasi sudah dibuat untuk tiket TKBM ini">
                                <i class="fa-solid fa-link"></i> Buka Order Koperasi TKBM
                            </a>
                        @else
                            <button onclick="document.getElementById('modalOrderKoperasi').classList.remove('hidden')" class="px-4 py-2 bg-[#1C325B] hover:bg-slate-900 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-lg transition">
                                <i class="fa-solid fa-code-branch"></i> Buat Order Koperasi (TKBM)
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                @forelse($order->containers as $c)
                    @php
                        $completedProgressCount = $c->progresses->where('status', 'OUT')->count() + $c->progresses->where('status', 'DONE')->count();
                        $totalProgress = $c->progresses->count();
                        $effectiveTkbm = $c->tkbm_option ?: $order->tkbm_option;
                    @endphp
                    <div class="{{ $c->is_cancelled ? 'bg-slate-100/50 opacity-80 border-slate-300' : 'bg-slate-50 hover:bg-blue-50/50 hover:border-blue-400 border-slate-200 shadow-sm hover:shadow' }} border rounded-2xl p-5 transition relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        
                        @if(!$c->is_cancelled)
                        <!-- Cancel Button (Top Right) -->
                        <div class="absolute top-3 right-3">
                            <form action="{{ route('requests.containers.cancel', [$order->id, $c->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MEMBATALKAN kontainer ini? Proses ini akan menghapus kontainer dari daftar tugas supir di lapangan.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-8 h-8 flex items-center justify-center bg-white border border-rose-200 text-rose-500 hover:bg-rose-500 hover:text-white rounded-full shadow-sm transition" title="Batalkan Kontainer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        </div>
                        @endif

                        <div class="space-y-2 pr-8">
                            <div class="flex flex-wrap items-center gap-2">
                                @if($c->is_cancelled)
                                    <span class="px-2.5 py-1 bg-slate-200 text-slate-700 font-extrabold rounded-lg text-xs uppercase tracking-wide border border-slate-300 flex items-center gap-1.5">
                                        <i class="fa-solid fa-ban text-[10px]"></i> DIBATALKAN
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-blue-100 text-blue-800 font-extrabold rounded-lg text-xs uppercase tracking-wide">
                                        {{ $c->container_size }} - {{ $c->container_type }}
                                    </span>
                                @endif
                                
                                @php
                                    $rawTkbm = $c->tkbm_option ?: ($order->tkbm_option ?? '');
                                    $tkbmDisplay = str_contains(strtolower($rawTkbm), 'forklift') ? 'Man Power + Forklift' : (strtolower($rawTkbm) == 'man power' ? 'Man Power (MP)' : $rawTkbm);
                                @endphp
                                @if($tkbmDisplay)
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-xs flex items-center gap-1 border border-amber-200">
                                        <i class="fa-solid fa-users-gear text-[10px]"></i> TKBM: {{ $tkbmDisplay }}
                                    </span>
                                @endif
                                @if(!empty($c->additional_services))
                                    @foreach($c->additional_services as $as)
                                        <span class="px-2 py-0.5 rounded text-xs font-bold border
                                            {{ $as == 'Railing' ? 'bg-purple-100 text-purple-800 border-purple-200' : '' }}
                                            {{ $as == 'LOLO' ? 'bg-sky-100 text-sky-800 border-sky-200' : '' }}
                                            {{ $as == 'Storage' ? 'bg-amber-100 text-amber-800 border-amber-200' : '' }}
                                            {{ $as == 'TKBM' ? 'bg-teal-100 text-teal-800 border-teal-200' : '' }}
                                            {{ !in_array($as, ['Railing', 'LOLO', 'Storage', 'TKBM']) ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : '' }}">
                                            +{{ $as }}
                                        </span>
                                    @endforeach
                                @endif
                                @if(strcasecmp($order->source, 'Koperasi') !== 0 && $c->is_pnbp)
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                        <i class="fa-solid fa-receipt text-[10px]"></i> PNBP Selesai
                                    </span>
                                @endif
                            </div>

                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Nomor Kontainer</div>
                                <div class="text-2xl font-black {{ $c->is_cancelled ? 'text-slate-500 line-through decoration-slate-400 decoration-2' : 'text-slate-800' }} tracking-tight">
                                    {{ $c->container_number ?? 'Tanpa Nomor' }}
                                </div>
                            </div>

                            @if(!$c->is_cancelled)
                                @if($totalProgress > 0)
                                    <div class="text-xs text-slate-600 font-medium">
                                        Progress Lapangan: <strong class="text-blue-700">{{ $completedProgressCount }} / {{ $totalProgress }} Selesai</strong>
                                    </div>
                                @else
                                    <div class="text-xs text-slate-400 italic">
                                        Belum ada aktivitas tiket pelaksana lapangan khusus
                                    </div>
                                @endif
                            @else
                                <div class="text-xs text-rose-500 font-bold italic">
                                    Kontainer ini tidak akan dikerjakan di lapangan.
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 sm:self-center mt-3 sm:mt-0">
                            @if(!$c->is_cancelled)
                                <button type="button" onclick="openContainerEditModal({{ $c->id }}, '{{ $c->container_number }}', '{{ $c->tkbm_option }}', '{{ $c->sp3kk_file_url }}')" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit Layanan Kontainer Ini</span>
                                </button>
                                <a href="{{ route('requests.containers.show', [$order->id, $c->id]) }}" class="px-5 py-2.5 bg-slate-900 hover:bg-blue-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition shadow-md whitespace-nowrap">
                                    <span>Detail & SubTask</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            @else
                                <button type="button" disabled class="px-4 py-2.5 bg-slate-200 text-slate-400 cursor-not-allowed font-bold rounded-xl text-xs flex items-center gap-1.5 border border-slate-300">
                                    <i class="fa-solid fa-ban"></i>
                                    <span>Dibatalkan</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 bg-slate-50 rounded-2xl border border-dashed border-slate-300 text-center text-slate-400 text-xs">
                        Tidak ada kontainer terdaftar untuk request ini.
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- Riwayat Perubahan Layanan & Surat Pendukung -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-file-contract text-blue-600"></i>
                Riwayat Perubahan Layanan & Surat Pendukung ({{ $order->serviceChanges->count() }})
            </h3>
            <button type="button" onclick="openEditServicesModal()" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <i class="fa-solid fa-plus-circle"></i> Tambah Perubahan Layanan Order
            </button>
        </div>

        <div class="space-y-3">
            @forelse($order->serviceChanges as $sc)
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 font-bold rounded text-xs">
                                {{ $sc->created_at->format('d M Y H:i') }}
                            </span>
                            @if($sc->container)
                                <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 font-extrabold rounded text-xs">
                                    Kontainer: {{ $sc->container->container_number }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 bg-slate-200 text-slate-800 font-bold rounded text-xs">
                                    Seluruh Order
                                </span>
                            @endif
                            <span class="text-xs font-bold text-slate-700">
                                Oleh: {{ $sc->changedBy ? $sc->changedBy->name : 'Admin System' }}
                            </span>
                        </div>

                        @if($sc->document_path)
                            @php
                                $docUrl = Str::startsWith($sc->document_path, ['http://', 'https://']) ? $sc->document_path : asset($sc->document_path);
                            @endphp
                            <a href="{{ $docUrl }}" target="_blank" class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg text-xs border border-emerald-200 flex items-center gap-1.5 transition">
                                <i class="fa-solid fa-file-arrow-down"></i>
                                <span>{{ $sc->document_name ?? 'Download Surat Pendukung' }}</span>
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs pt-2 border-t border-slate-200/80">
                        <div>
                            <span class="text-slate-400 font-medium">Perubahan TKBM:</span>
                            <div class="font-bold text-slate-800">
                                {{ $sc->old_tkbm_option ?? 'Awal' }} <i class="fa-solid fa-arrow-right text-[10px] text-slate-400 mx-1"></i> {{ $sc->new_tkbm_option ?? 'Tetap' }}
                            </div>
                        </div>

                        <div>
                            <span class="text-slate-400 font-medium">Layanan Ditambahkan:</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @if(!empty($sc->added_services))
                                    @foreach($sc->added_services as $as)
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold border
                                            {{ $as == 'Railing' ? 'bg-purple-100 text-purple-800 border-purple-200' : '' }}
                                            {{ $as == 'LOLO' ? 'bg-sky-100 text-sky-800 border-sky-200' : '' }}
                                            {{ $as == 'Storage' ? 'bg-amber-100 text-amber-800 border-amber-200' : '' }}
                                            {{ $as == 'TKBM' ? 'bg-teal-100 text-teal-800 border-teal-200' : '' }}
                                            {{ !in_array($as, ['Railing', 'LOLO', 'Storage', 'TKBM']) ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : '' }}">
                                            +{{ $as }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-slate-400 font-normal text-xs">Tidak ada layanan baru</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <span class="text-slate-400 font-medium">Catatan Lapangan:</span>
                            <div class="text-slate-700 italic">
                                {{ $sc->notes ?? 'Tanpa catatan' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-400 italic">
                    Belum ada riwayat perubahan layanan atau surat pendukung diunggah.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal Popup Edit Layanan Order Utama -->
<div id="editServicesModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl space-y-0">
        <div class="p-5 bg-[#1C325B] text-white flex items-center justify-between">
            <div>
                <h4 class="font-bold text-base">Edit / Tambah Layanan Order</h4>
                <p class="text-xs text-slate-300 mt-0.5">Order: {{ $order->order_number }} ({{ $order->nama_pt }})</p>
            </div>
            <button type="button" onclick="closeEditServicesModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('requests.updateServices', $order->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5 max-h-[82vh] overflow-y-auto">
            @csrf
            
            @php
                $existingOrderServices = $order->subTasks->pluck('service_type')->toArray();
                $hasRailing = in_array('Railing', $existingOrderServices);
                $hasLolo = in_array('LOLO', $existingOrderServices);
                $hasStorage = in_array('Storage', $existingOrderServices);
                $hasTkbm = in_array('TKBM', $existingOrderServices) || !empty($order->tkbm_option);
                $currentTkbm = $order->tkbm_option ?? 'Man Power';
            @endphp

            <div class="space-y-3">
                <!-- 1. HAULAGE CARD -->
                <div class="p-4 bg-[#EAF0F6] border border-[#CBD5E1] rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200/80 text-[#1C325B] flex items-center justify-center">
                                <i class="fa-solid fa-truck-front text-lg"></i>
                            </div>
                            <span class="font-bold text-slate-800 text-sm">Railing</span>
                        </div>
                        @if($hasRailing)
                            <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                                <i class="fa-solid fa-check text-xs font-black"></i>
                            </div>
                            <input type="hidden" name="existing_services[]" value="Railing">
                        @else
                            <label class="cursor-pointer">
                                <input type="checkbox" name="added_services[]" value="Railing" class="w-5 h-5 text-blue-900 rounded border-slate-300 focus:ring-blue-600">
                            </label>
                        @endif
                    </div>

                    <!-- Dokumen Railing -->
                    <div class="pt-2 border-t border-slate-300/60">
                        <div class="text-xs font-semibold text-slate-600 mb-1.5">Dokumen Railing</div>
                        <input type="file" id="order_railing_file" name="supporting_letter" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleFileSelected(this, 'order_railing_label')">
                        <div onclick="triggerFileInput('order_railing_file')" class="bg-white rounded-xl border border-slate-200 p-3.5 flex items-center justify-between cursor-pointer hover:border-blue-400 transition shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-file-arrow-up"></i>
                                </div>
                                <div>
                                    <div id="order_railing_label" class="text-xs font-bold text-slate-800">Upload SP2 (PDF / JPG / PNG)</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Format: PDF, JPG, JPEG, PNG</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- 2. LOLO CARD -->
                <div class="p-4 bg-[#EAF0F6] border border-[#CBD5E1] rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-200/80 text-[#1C325B] flex items-center justify-center">
                            <i class="fa-solid fa-dolly text-lg"></i>
                        </div>
                        <span class="font-bold text-slate-800 text-sm">LOLO</span>
                    </div>
                    @if($hasLolo)
                        <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                            <i class="fa-solid fa-check text-xs font-black"></i>
                        </div>
                        <input type="hidden" name="existing_services[]" value="LOLO">
                    @else
                        <label class="cursor-pointer">
                            <input type="checkbox" name="added_services[]" value="LOLO" class="w-5 h-5 text-blue-900 rounded border-slate-300 focus:ring-blue-600">
                        </label>
                    @endif
                </div>

                <!-- 3. PENUMPUKAN CARD -->
                <div class="p-4 bg-[#EAF0F6] border border-[#CBD5E1] rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-200/80 text-[#1C325B] flex items-center justify-center">
                            <i class="fa-solid fa-layer-group text-lg"></i>
                        </div>
                        <span class="font-bold text-slate-800 text-sm">Storage</span>
                    </div>
                    @if($hasStorage)
                        <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                            <i class="fa-solid fa-check text-xs font-black"></i>
                        </div>
                        <input type="hidden" name="existing_services[]" value="Storage">
                    @else
                        <label class="cursor-pointer">
                            <input type="checkbox" name="added_services[]" value="Storage" class="w-5 h-5 text-blue-900 rounded border-slate-300 focus:ring-blue-600">
                        </label>
                    @endif
                </div>

                <!-- 4. TKBM CARD -->
                <div class="p-4 bg-[#EAF0F6] border border-[#CBD5E1] rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200/80 text-[#1C325B] flex items-center justify-center">
                                <i class="fa-solid fa-shield-halved text-lg"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-800 text-sm">TKBM</span>
                                @if($currentTkbm)
                                    <div class="mt-0.5">
                                        @if($currentTkbm == 'Man Power + Forklift')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                                <i class="fa-solid fa-forklift text-[9px]"></i> Man Power + Fork Lift
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                                                <i class="fa-solid fa-people-carry-box text-[9px]"></i> Man Power
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($hasTkbm)
                            <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                                <i class="fa-solid fa-check text-xs font-black"></i>
                            </div>
                            <input type="hidden" name="existing_services[]" value="TKBM">
                        @else
                            <label class="cursor-pointer">
                                <input type="checkbox" name="added_services[]" value="TKBM" class="w-5 h-5 text-blue-900 rounded border-slate-300 focus:ring-blue-600">
                            </label>
                        @endif
                    </div>

                    <!-- Sub-content: Lokasi TKBM (Opsi TKBM yang bisa dipilih & diganti) -->
                    <div class="pt-2 border-t border-slate-300/60 space-y-2">
                        <div class="text-xs font-semibold text-slate-600 mb-1.5">Lokasi TKBM</div>
                        <input type="hidden" id="order_tkbm_option_input" name="tkbm_option" value="{{ $currentTkbm }}">
                        
                        <!-- Radio Option 1: Man Power -->
                        <div onclick="selectTkbmOption('order', 'Man Power')" data-option="Man Power" class="order-tkbm-card p-3 rounded-xl border flex items-center gap-3 cursor-pointer transition shadow-sm {{ $currentTkbm == 'Man Power' ? 'border-2 border-[#1C325B] bg-slate-200/90' : 'border-slate-200 bg-white' }}">
                            <div class="tkbm-dot-border w-5 h-5 rounded-full border-2 {{ $currentTkbm == 'Man Power' ? 'border-[#1C325B]' : 'border-slate-300 bg-white' }} flex items-center justify-center">
                                <div class="tkbm-dot-inner w-2.5 h-2.5 rounded-full bg-[#1C325B]" style="display: {{ $currentTkbm == 'Man Power' ? 'block' : 'none' }}"></div>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Man Power</span>
                        </div>

                        <!-- Radio Option 2: Man Power + Forklift -->
                        <div onclick="selectTkbmOption('order', 'Man Power + Forklift')" data-option="Man Power + Forklift" class="order-tkbm-card p-3 rounded-xl border flex items-center gap-3 cursor-pointer transition shadow-sm {{ $currentTkbm == 'Man Power + Forklift' ? 'border-2 border-[#1C325B] bg-slate-200/90' : 'border-slate-200 bg-white' }}">
                            <div class="tkbm-dot-border w-5 h-5 rounded-full border-2 {{ $currentTkbm == 'Man Power + Forklift' ? 'border-[#1C325B]' : 'border-slate-300 bg-white' }} flex items-center justify-center">
                                <div class="tkbm-dot-inner w-2.5 h-2.5 rounded-full bg-[#1C325B]" style="display: {{ $currentTkbm == 'Man Power + Forklift' ? 'block' : 'none' }}"></div>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Man Power + Forklift</span>
                        </div>
                    </div>
                </div>

                <!-- 5. ASURANSI CARD -->
                <div class="p-4 bg-emerald-50/80 border border-emerald-200 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                <i class="fa-solid fa-shield-halved text-lg"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-800 text-sm">Asuransi</span>
                                <div class="text-[10px] text-slate-500 mt-0.5">Perlindungan nilai muatan / cargo</div>
                            </div>
                        </div>
                        @if($order->has_asuransi)
                            <div class="w-6 h-6 bg-emerald-600 text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Asuransi sudah aktif">
                                <i class="fa-solid fa-check text-xs font-black"></i>
                            </div>
                            <input type="hidden" name="existing_services[]" value="Asuransi">
                        @else
                            <label class="cursor-pointer">
                                <input type="checkbox" name="added_services[]" value="Asuransi" id="order_asuransi_cb" class="w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" onchange="document.getElementById('order_asuransi_value_wrap').classList.toggle('hidden', !this.checked)">
                            </label>
                        @endif
                    </div>

                    <div id="order_asuransi_value_wrap" class="{{ $order->has_asuransi ? '' : 'hidden' }} pt-2 border-t border-emerald-200/60">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nilai Pertanggungan (Rp)</label>
                        <input type="number" name="asuransi_value" value="{{ $order->asuransi_value }}" placeholder="misal: 50000000" min="0" step="1000" class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- 6. DOKUMEN SPK / SURAT PERMOHONAN TERUPLOAD -->
                <div class="p-4 bg-sky-50/80 border border-sky-200 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
                                <i class="fa-solid fa-file-contract text-lg"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-800 text-sm">Dokumen SPK Terupload</span>
                                <div class="text-[10px] text-slate-500 mt-0.5">Surat Perintah Kerja / Berkas Permohonan</div>
                            </div>
                        </div>
                        @if($order->railing_file_path || (is_array($order->cargo_file_path) && !empty($order->cargo_file_path)))
                            @php
                                $spkUrl = $order->railing_file_path ? asset($order->railing_file_path) : asset($order->cargo_file_path[0]);
                            @endphp
                            <a href="{{ $spkUrl }}" target="_blank" class="px-2.5 py-1 bg-sky-600 text-white hover:bg-sky-700 font-bold rounded-lg text-xs flex items-center gap-1 shadow-sm transition">
                                <i class="fa-solid fa-eye"></i> Lihat SPK
                            </a>
                        @else
                            <span class="px-2.5 py-1 bg-slate-200 text-slate-600 font-semibold rounded-lg text-xs">Belum Ada</span>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-sky-200/60">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Upload / Ganti Dokumen SPK</label>
                        <input type="file" name="spk_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-600 file:text-white hover:file:bg-sky-700 cursor-pointer">
                    </div>
                </div>
            </div>

            <div class="space-y-3 pt-3 border-t border-slate-200">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama / Keterangan Surat Pendukung</label>
                    <input type="text" name="document_name" placeholder="misal: Surat Perubahan TKBM" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Lapangan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan perubahan..." class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeEditServicesModal()" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#1C325B] hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Popup Edit Layanan Khusus Kontainer Individual -->
<div id="containerEditModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl space-y-0">
        <div class="p-5 bg-[#1C325B] text-white flex items-center justify-between">
            <div>
                <h4 class="font-bold text-base">Edit Layanan Khusus Kontainer</h4>
                <p id="containerEditTitle" class="text-xs text-amber-300 mt-0.5">Kontainer: -</p>
            </div>
            <button type="button" onclick="closeContainerEditModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="containerEditForm" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5 max-h-[82vh] overflow-y-auto">
            @csrf

            <div class="space-y-3">
                <!-- 1. HAULAGE CARD -->
                <div class="p-4 bg-[#EAF0F6] border border-[#CBD5E1] rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200/80 text-[#1C325B] flex items-center justify-center">
                                <i class="fa-solid fa-truck-front text-lg"></i>
                            </div>
                            <span class="font-bold text-slate-800 text-sm">Railing</span>
                        </div>
                        <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                            <i class="fa-solid fa-check text-xs font-black"></i>
                        </div>
                    </div>

                    <!-- Dokumen Railing -->
                    <div class="pt-2 border-t border-slate-300/60">
                        <div class="text-xs font-semibold text-slate-600 mb-1.5">Dokumen Railing</div>
                        <input type="file" id="container_railing_file" name="supporting_letter" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleFileSelected(this, 'container_railing_label')">
                        <div onclick="triggerFileInput('container_railing_file')" class="bg-white rounded-xl border border-slate-200 p-3.5 flex items-center justify-between cursor-pointer hover:border-blue-400 transition shadow-sm mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-file-arrow-up"></i>
                                </div>
                                <div>
                                    <div id="container_railing_label" class="text-xs font-bold text-slate-800">Upload SP2 (PDF / JPG / PNG)</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Format: PDF, JPG, JPEG, PNG</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-slate-400 text-xs"></i>
                        </div>

                        <input type="file" id="container_sp3kk_file" name="sp3kk_file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleFileSelected(this, 'container_sp3kk_label')">
                        <div onclick="triggerFileInput('container_sp3kk_file')" class="bg-white rounded-xl border border-slate-200 p-3.5 flex items-center justify-between cursor-pointer hover:border-emerald-400 transition shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <div>
                                    <div id="container_sp3kk_label" class="text-xs font-bold text-slate-800">Upload SP3KK (PDF / JPG / PNG)</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Opsional</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a id="container_sp3kk_view" href="#" target="_blank" class="hidden text-[10px] font-bold text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition" onclick="event.stopPropagation();">
                                    <i class="fa-solid fa-eye me-1"></i>Lihat
                                </a>
                                <i class="fa-solid fa-chevron-right text-slate-400 text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. LOLO CARD -->
                <div class="p-4 bg-[#EAF0F6] border border-[#CBD5E1] rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-200/80 text-[#1C325B] flex items-center justify-center">
                            <i class="fa-solid fa-dolly text-lg"></i>
                        </div>
                        <span class="font-bold text-slate-800 text-sm">LOLO</span>
                    </div>
                    <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                        <i class="fa-solid fa-check text-xs font-black"></i>
                    </div>
                </div>

                <!-- 3. PENUMPUKAN CARD -->
                <div class="p-4 bg-[#EAF0F6] border border-[#CBD5E1] rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-200/80 text-[#1C325B] flex items-center justify-center">
                            <i class="fa-solid fa-layer-group text-lg"></i>
                        </div>
                        <span class="font-bold text-slate-800 text-sm">Storage</span>
                    </div>
                    <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                        <i class="fa-solid fa-check text-xs font-black"></i>
                    </div>
                </div>

                <!-- 4. TKBM CARD -->
                <div class="p-4 bg-[#EAF0F6] border border-[#CBD5E1] rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200/80 text-[#1C325B] flex items-center justify-center">
                                <i class="fa-solid fa-shield-halved text-lg"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-800 text-sm">TKBM</span>
                                @if($currentTkbm)
                                    <div class="mt-0.5">
                                        @if($currentTkbm == 'Man Power + Forklift')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                                <i class="fa-solid fa-forklift text-[9px]"></i> Man Power + Fork Lift
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                                                <i class="fa-solid fa-people-carry-box text-[9px]"></i> Man Power
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                            <i class="fa-solid fa-check text-xs font-black"></i>
                        </div>
                    </div>

                    <!-- Sub-content: Lokasi TKBM (Opsi TKBM yang bisa dipilih & diganti) -->
                    <div class="pt-2 border-t border-slate-300/60 space-y-2">
                        <div class="text-xs font-semibold text-slate-600 mb-1.5">Lokasi TKBM</div>
                        <input type="hidden" id="container_tkbm_option_input" name="tkbm_option" value="Man Power">
                        
                        <!-- Radio Option 1: Man Power -->
                        <div onclick="selectTkbmOption('container', 'Man Power')" data-option="Man Power" class="container-tkbm-card p-3 rounded-xl border flex items-center gap-3 cursor-pointer transition shadow-sm border-2 border-[#1C325B] bg-slate-200/90">
                            <div class="tkbm-dot-border w-5 h-5 rounded-full border-2 border-[#1C325B] flex items-center justify-center">
                                <div class="tkbm-dot-inner w-2.5 h-2.5 rounded-full bg-[#1C325B]"></div>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Man Power</span>
                        </div>

                        <!-- Radio Option 2: Man Power + Forklift -->
                        <div onclick="selectTkbmOption('container', 'Man Power + Forklift')" data-option="Man Power + Forklift" class="container-tkbm-card p-3 rounded-xl border flex items-center gap-3 cursor-pointer transition shadow-sm border-slate-200 bg-white">
                            <div class="tkbm-dot-border w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center">
                                <div class="tkbm-dot-inner w-2.5 h-2.5 rounded-full bg-[#1C325B]" style="display: none"></div>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Man Power + Forklift</span>
                        </div>
                    </div>
                </div>

                <!-- 5. ASURANSI CARD (Container) -->
                <div class="p-4 bg-emerald-50/80 border border-emerald-200 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                <i class="fa-solid fa-shield-halved text-lg"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-800 text-sm">Asuransi</span>
                                <div class="text-[10px] text-slate-500 mt-0.5">Perlindungan nilai muatan / cargo</div>
                            </div>
                        </div>
                        @if($order->has_asuransi)
                            <div class="w-6 h-6 bg-emerald-600 text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Asuransi sudah aktif">
                                <i class="fa-solid fa-check text-xs font-black"></i>
                            </div>
                            <input type="hidden" name="existing_services[]" value="Asuransi">
                        @else
                            <label class="cursor-pointer">
                                <input type="checkbox" name="added_services[]" value="Asuransi" id="container_asuransi_cb" class="w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" onchange="document.getElementById('container_asuransi_value_wrap').classList.toggle('hidden', !this.checked)">
                            </label>
                        @endif
                    </div>

                    <div id="container_asuransi_value_wrap" class="{{ $order->has_asuransi ? '' : 'hidden' }} pt-2 border-t border-emerald-200/60">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nilai Pertanggungan (Rp)</label>
                        <input type="number" name="asuransi_value" value="{{ $order->asuransi_value }}" placeholder="misal: 50000000" min="0" step="1000" class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <div class="space-y-3 pt-3 border-t border-slate-200">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama / Keterangan Surat Pendukung Lapangan</label>
                    <input type="text" name="document_name" placeholder="misal: Surat Tambahan TKBM Kontainer A" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Lapangan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan perubahan spesifik kontainer..." class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeContainerEditModal()" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#1C325B] hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow">Simpan Perubahan Kontainer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal PNBP Check / Uncheck & Notes Submission for Cargo -->
<div id="pnbpCargoModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl space-y-0">
        <!-- Modal Header -->
        <div class="p-5 bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-400/30 flex items-center justify-center text-amber-400 font-bold">
                    <i class="fa-solid fa-receipt text-lg"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-base">Status & Catatan PNBP Muatan Cargo</h4>
                    <p class="text-xs text-slate-300">Order: {{ $order->order_number }} ({{ $order->nama_pt }})</p>
                </div>
            </div>
            <button type="button" onclick="closePnbpCargoModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="pnbpCargoForm" action="{{ route('requests.togglePnbp', $order->id) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PATCH')

            <!-- Hidden input for boolean is_pnbp -->
            <input type="hidden" name="is_pnbp" id="pnbp_cargo_is_completed" value="{{ $order->is_pnbp ? '1' : '0' }}">

            <!-- Status PNBP Toggle Selector (Checked vs Unchecked) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Pilih Status PNBP Cargo *</label>
                <div class="grid grid-cols-2 gap-3">
                    <!-- Option 1: Selesai / Checked -->
                    <div id="pnbp_cargo_opt_done" onclick="selectPnbpCargoStatus(true)" class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col items-center justify-center text-center gap-2 {{ $order->is_pnbp ? 'border-emerald-600 bg-emerald-50 text-emerald-800 font-bold shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        <div class="w-10 h-10 rounded-full {{ $order->is_pnbp ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center font-black text-lg transition">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <div class="text-xs font-black">PNBP Selesai</div>
                            <div class="text-[10px] text-slate-400 font-normal mt-0.5">Sudah Terbit / Lunas</div>
                        </div>
                    </div>

                    <!-- Option 2: Belum Selesai / Unchecked -->
                    <div id="pnbp_cargo_opt_pending" onclick="selectPnbpCargoStatus(false)" class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col items-center justify-center text-center gap-2 {{ !$order->is_pnbp ? 'border-rose-600 bg-rose-50 text-rose-800 font-bold shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        <div class="w-10 h-10 rounded-full {{ !$order->is_pnbp ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center font-black text-lg transition">
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                        <div>
                            <div class="text-xs font-black">Belum Selesai</div>
                            <div class="text-[10px] text-slate-400 font-normal mt-0.5">Pending / Belum Terbit</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nomor PNBP Input -->
            <div id="pnbp_cargo_number_wrap" class="{{ $order->is_pnbp ? '' : 'opacity-60' }} transition">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Nomor PNBP / Billing <span class="text-slate-400 font-normal normal-case">(Otomatis dibuat jika kosong)</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs">
                        <i class="fa-solid fa-hashtag"></i>
                    </span>
                    <input type="text" name="pnbp_number" id="pnbp_cargo_number_input" value="{{ $order->pnbp_number }}" placeholder="misal: PNBP/{{ date('Ymd') }}/{{ sprintf('%04d', $order->id) }}" class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono">
                </div>
            </div>

            <!-- Notes Submission PNBP Input -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Catatan / Notes Submission PNBP Cargo <span class="text-slate-400 font-normal normal-case">(Opsional)</span>
                </label>
                <textarea name="pnbp_note" id="pnbp_cargo_note_input" rows="3" placeholder="Masukkan catatan atau instruksi submission PNBP cargo..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">{{ $order->pnbp_note }}</textarea>
            </div>

            <!-- Info Bar -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-500 flex items-start gap-2.5">
                <i class="fa-solid fa-circle-info text-blue-600 mt-0.5"></i>
                <div class="text-[11px] leading-relaxed">
                    Perubahan status PNBP muatan Cargo dan catatan submission ini akan tersimpan real-time dan tercatat pada riwayat operasional order.
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closePnbpCargoModal()" class="px-4 py-2.5 bg-slate-100 text-slate-600 font-semibold rounded-xl text-xs hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Status PNBP Cargo</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Popup Viewer Foto Bukti -->
<div id="photoModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl space-y-4">
        <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
            <h4 id="photoModalTitle" class="font-bold text-sm">Foto Bukti</h4>
            <button onclick="closePhotoModal()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-300 hover:text-white transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 flex flex-col items-center justify-center bg-slate-100 min-h-[300px]">
            <img id="photoModalImg" src="" alt="Bukti Foto" class="max-h-[70vh] w-auto rounded-2xl object-contain shadow-lg border border-slate-200">
        </div>

        <div class="p-4 border-t border-slate-100 flex items-center justify-between bg-slate-50">
            <span class="text-xs text-slate-500 font-medium">Klik kanan atau tombol di samping untuk mengunduh</span>
            <a id="photoModalDownload" href="" download target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition flex items-center gap-2 shadow-lg shadow-blue-600/30">
                <i class="fa-solid fa-download"></i>
                <span>Download Foto</span>
            </a>
        </div>
    </div>
</div>

<script>
    function openPnbpCargoModal() {
        document.getElementById('pnbpCargoModal').classList.remove('hidden');
    }

    function closePnbpCargoModal() {
        document.getElementById('pnbpCargoModal').classList.add('hidden');
    }

    function selectPnbpCargoStatus(isDone) {
        document.getElementById('pnbp_cargo_is_completed').value = isDone ? '1' : '0';
        const optDone = document.getElementById('pnbp_cargo_opt_done');
        const optPending = document.getElementById('pnbp_cargo_opt_pending');
        const numWrap = document.getElementById('pnbp_cargo_number_wrap');

        const doneIcon = optDone.querySelector('div');
        const pendingIcon = optPending.querySelector('div');

        if (isDone) {
            optDone.className = 'p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col items-center justify-center text-center gap-2 border-emerald-600 bg-emerald-50 text-emerald-800 font-bold shadow-sm';
            doneIcon.className = 'w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-black text-lg transition';

            optPending.className = 'p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col items-center justify-center text-center gap-2 border-slate-200 bg-white text-slate-600 hover:border-slate-300';
            pendingIcon.className = 'w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center font-black text-lg transition';

            numWrap.classList.remove('opacity-60');
        } else {
            optDone.className = 'p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col items-center justify-center text-center gap-2 border-slate-200 bg-white text-slate-600 hover:border-slate-300';
            doneIcon.className = 'w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center font-black text-lg transition';

            optPending.className = 'p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col items-center justify-center text-center gap-2 border-rose-600 bg-rose-50 text-rose-800 font-bold shadow-sm';
            pendingIcon.className = 'w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center font-black text-lg transition';

            numWrap.classList.add('opacity-60');
        }
    }

    function openPhotoModal(imgUrl, title) {
        document.getElementById('photoModalTitle').innerText = title;
        document.getElementById('photoModalImg').src = imgUrl;
        document.getElementById('photoModalDownload').href = imgUrl;
        document.getElementById('photoModal').classList.remove('hidden');
    }

    function closePhotoModal() {
        document.getElementById('photoModal').classList.add('hidden');
        document.getElementById('photoModalImg').src = '';
    }

    function openEditServicesModal() {
        document.getElementById('editServicesModal').classList.remove('hidden');
    }

    function closeEditServicesModal() {
        document.getElementById('editServicesModal').classList.add('hidden');
    }

    function openContainerEditModal(containerId, containerNumber, currentTkbm, sp3kkFileUrl) {
        var actionUrl = "{{ url('requests/' . $order->id . '/containers') }}/" + containerId + "/update-services";
        document.getElementById('containerEditForm').action = actionUrl;
        document.getElementById('containerEditTitle').innerText = "Kontainer: " + (containerNumber || ("ID-" + containerId));
        
        var viewBtn = document.getElementById('container_sp3kk_view');
        if (sp3kkFileUrl && sp3kkFileUrl.trim() !== '') {
            viewBtn.href = sp3kkFileUrl;
            viewBtn.classList.remove('hidden');
            document.getElementById('container_sp3kk_label').innerText = "Ganti SP3KK";
        } else {
            viewBtn.classList.add('hidden');
            document.getElementById('container_sp3kk_label').innerText = "Upload SP3KK (PDF / JPG / PNG)";
        }
        
        if (currentTkbm) {
            selectTkbmOption('container', currentTkbm);
        } else {
            selectTkbmOption('container', 'Man Power');
        }
        document.getElementById('containerEditModal').classList.remove('hidden');
    }

    function closeContainerEditModal() {
        document.getElementById('containerEditModal').classList.add('hidden');
    }

    function selectTkbmOption(containerPrefix, optionValue) {
        var hiddenInput = document.getElementById(containerPrefix + '_tkbm_option_input');
        if (hiddenInput) {
            hiddenInput.value = optionValue;
        }

        var cards = document.querySelectorAll('.' + containerPrefix + '-tkbm-card');
        cards.forEach(function(card) {
            var opt = card.getAttribute('data-option');
            var dotInner = card.querySelector('.tkbm-dot-inner');
            var dotBorder = card.querySelector('.tkbm-dot-border');

            if (opt === optionValue) {
                card.className = containerPrefix + '-tkbm-card p-3 rounded-xl border-2 border-[#1C325B] bg-slate-200/90 flex items-center gap-3 cursor-pointer transition shadow-sm';
                if (dotBorder) {
                    dotBorder.className = 'tkbm-dot-border w-5 h-5 rounded-full border-2 border-[#1C325B] flex items-center justify-center';
                }
                if (dotInner) {
                    dotInner.style.display = 'block';
                }
            } else {
                card.className = containerPrefix + '-tkbm-card p-3 rounded-xl border border-slate-200 bg-white flex items-center gap-3 cursor-pointer hover:border-slate-300 transition';
                if (dotBorder) {
                    dotBorder.className = 'tkbm-dot-border w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center';
                }
                if (dotInner) {
                    dotInner.style.display = 'none';
                }
            }
        });
    }

    function triggerFileInput(inputId) {
        var el = document.getElementById(inputId);
        if (el) el.click();
    }

    function handleFileSelected(input, labelId) {
        if (input.files && input.files[0]) {
            var el = document.getElementById(labelId);
            if (el) el.innerText = "File Dipilih: " + input.files[0].name;
        }
    }

    // ===== AJAX STATUS UPDATE & REAL-TIME POLLING FOR CARGO =====
    function submitStatusAjax(event, stId) {
        event.preventDefault();

        const form = document.getElementById('statusForm-' + stId);
        const submitBtn = document.getElementById('submitBtn-' + stId);
        const btnText = submitBtn.querySelector('.btn-text');
        const btnSpinner = submitBtn.querySelector('.btn-spinner');
        const msgDiv = document.getElementById('statusMsg-' + stId);

        // Show spinner, disable button
        btnText.style.display = 'none';
        btnSpinner.classList.remove('hidden');
        submitBtn.disabled = true;

        // Hide and reset message
        msgDiv.classList.add('hidden');
        msgDiv.className = 'hidden text-xs font-bold px-3 py-2 mt-2 rounded-xl flex items-center gap-2 transition-all duration-300';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(res => {
            // Show success message
            msgDiv.textContent = res.message;
            msgDiv.classList.remove('hidden');
            msgDiv.classList.add('bg-emerald-50', 'text-emerald-800', 'border', 'border-emerald-200');

            // Reset inputs
            const noteInput = form.querySelector('input[name="note"]');
            if (noteInput) noteInput.value = '';
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput) fileInput.value = '';

            // Update UI immediately
            pollCargoUpdates();

            // Hide success message after 3 seconds
            setTimeout(() => {
                msgDiv.classList.add('opacity-0');
                setTimeout(() => {
                    msgDiv.classList.add('hidden');
                    msgDiv.classList.remove('opacity-0', 'bg-emerald-50', 'text-emerald-800', 'border', 'border-emerald-200');
                }, 300);
            }, 3000);
        })
        .catch(err => {
            console.error(err);
            msgDiv.textContent = err.message || 'Terjadi kesalahan saat menyimpan status.';
            msgDiv.classList.remove('hidden');
            msgDiv.classList.add('bg-rose-50', 'text-rose-800', 'border', 'border-rose-200');
        })
        .finally(() => {
            btnText.style.display = 'block';
            btnSpinner.classList.add('hidden');
            submitBtn.disabled = false;
        });

        return false;
    }

    function pollCargoUpdates() {
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) return;
            return response.text();
        })
        .then(html => {
            if (!html) return;
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Update subtask displays
            document.querySelectorAll('[id^="subtask-display-"]').forEach(displayEl => {
                const id = displayEl.id;
                const newDisplayEl = doc.getElementById(id);
                if (newDisplayEl) {
                    displayEl.innerHTML = newDisplayEl.innerHTML;
                }
            });

            // Update select values if not focused
            document.querySelectorAll('[id^="statusSelect-"]').forEach(selectEl => {
                const id = selectEl.id;
                const newSelectEl = doc.getElementById(id);
                if (newSelectEl && document.activeElement !== selectEl) {
                    selectEl.value = newSelectEl.value;
                }
            });
        })
        .catch(err => console.error("Error polling cargo updates:", err));
    }

    // Set polling every 4 seconds for cargo progress
    setInterval(pollCargoUpdates, 4000);
</script>

<!-- Modal Order Koperasi -->
@if(strtolower($order->source) == 'all in')
<div id="modalOrderKoperasi" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modalOrderKoperasi').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Buat Order Koperasi (TKBM)</h3>
            <button onclick="document.getElementById('modalOrderKoperasi').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('requests.createKoperasiFromAllIn', $order->id) }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div class="p-3 bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-xl leading-relaxed">
                    Mohon lengkapi detail order untuk diteruskan ke Koperasi TKBM.
                </div>
                
                @php
                    $isBintangKepriJaya = (stripos($order->nama_pt, 'bintang kepri jaya') !== false || stripos($order->customer->name, 'bintang kepri jaya') !== false || stripos($order->customer->default_nama_pt, 'bintang kepri jaya') !== false);
                    
                    // Auto fill if PT Bintang Kepri Jaya
                    $vessel = $isBintangKepriJaya ? $order->vessel : '';
                    $voyage = $isBintangKepriJaya ? $order->voyage : '';
                    $no_surat_jalan = $isBintangKepriJaya ? $order->no_surat_jalan : '';
                    $no_bp = $isBintangKepriJaya ? $order->no_bp : '';
                    $nomor_container_cargo = $isBintangKepriJaya ? $order->nomor_container_cargo : '';
                    $jenis_barang = $isBintangKepriJaya ? $order->jenis_barang : '';
                    $jumlah_barang = $isBintangKepriJaya ? $order->jumlah_barang : '';
                    $jumlah_tonase = $isBintangKepriJaya ? $order->jumlah_tonase : '';
                    $nomor_bl = $isBintangKepriJaya ? $order->nomor_bl : '';
                @endphp
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[50vh] overflow-y-auto p-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vessel</label>
                        <input type="text" name="vessel" value="{{ $vessel }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Voyage</label>
                        <input type="text" name="voyage" value="{{ $voyage }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Surat Jalan</label>
                        <input type="text" name="no_surat_jalan" value="{{ $no_surat_jalan }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. BP</label>
                        <input type="text" name="no_bp" value="{{ $no_bp }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Container / Cargo</label>
                        <input type="text" name="nomor_container_cargo" value="{{ $nomor_container_cargo }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Barang</label>
                        <input type="text" name="jenis_barang" value="{{ $jenis_barang }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Barang</label>
                        <input type="text" name="jumlah_barang" value="{{ $jumlah_barang }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Tonase (Ton)</label>
                        <input type="number" step="0.1" name="jumlah_tonase" value="{{ $jumlah_tonase }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor BL</label>
                        <input type="text" name="nomor_bl" value="{{ $nomor_bl }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>

                @if(!$isBintangKepriJaya)
                    <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 text-[11px] rounded-xl font-medium">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Customer bukan PT Bintang Kepri Jaya, field sengaja dikosongkan untuk Anda isi manual.
                    </div>
                @endif
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50">
                <button type="button" onclick="document.getElementById('modalOrderKoperasi').classList.add('hidden')" class="px-4 py-2 text-slate-600 font-bold text-xs hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-lg transition">Buat Order Sekarang</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
