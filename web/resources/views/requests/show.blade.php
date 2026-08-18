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
        <div class="flex items-center gap-3">
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

    <!-- Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
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
            <div class="text-base font-bold text-slate-800 mt-1">{{ $order->tanggal_order->format('d F Y') }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Payload: {{ $order->payload_type }}</div>
        </div>

        <div>
            <div class="text-xs text-slate-400 font-bold uppercase">Layanan & TKBM (Order Utama)</div>
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
                        <i class="fa-solid fa-shield-halved"></i> Asuransi Cargo Aktif
                    </span>
                @else
                    <span class="text-slate-400 font-normal text-xs">Tanpa Asuransi</span>
                @endif
            </div>
        </div>
    </div>

    @if(strtolower($order->payload_type) === 'cargo')
        <!-- LEVEL 2: Detail Payload Cargo -->
        <div class="bg-white rounded-2xl border border-amber-200/80 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-amber-100 pb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <i class="fa-solid fa-boxes-packing text-amber-600"></i>
                        Rincian Muatan Cargo & Dokumen Manifest
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Order ini berjenis Cargo (Muatan Bebas / General Cargo) tanpa nomor kontainer baku.</p>
                </div>
                <span class="px-3 py-1 bg-amber-100 text-amber-800 border border-amber-300 font-bold rounded-xl text-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-boxes-packing"></i> PAYLOAD CARGO
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Barang</div>
                    <div class="text-base font-black text-slate-800">{{ $order->jenis_barang ?: 'General Cargo' }}</div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jumlah Tonase</div>
                    <div class="text-base font-black text-slate-800">{{ $order->jumlah_tonase ? str_replace('.', ',', (float)$order->jumlah_tonase) . ' Ton' : '-' }}</div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">No. Container Cargo (Ref)</div>
                    <div class="text-base font-black text-slate-800">{{ $order->nomor_container_cargo ?: '-' }}</div>
                </div>
            </div>

            <div class="p-4 bg-amber-50/60 rounded-xl border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-800">Lampiran Dokumen Manifest Cargo</div>
                        <div class="text-xs text-slate-500">
                            @if($order->cargo_file_path)
                                Terlampir: {{ basename($order->cargo_file_path) }} (Otomatis menjadi Hal. 2 Surat Permohonan)
                            @else
                                Belum ada berkas manifest yang diunggah
                            @endif
                        </div>
                    </div>
                </div>

                @if($order->cargo_file_path)
                    <div class="flex items-center gap-2">
                        <a href="{{ asset($order->cargo_file_path) }}" target="_blank" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>Buka Berkas Manifest</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
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
            </div>

            <div class="space-y-4">
                @forelse($order->containers as $c)
                    @php
                        $completedProgressCount = $c->progresses->where('status', 'OUT')->count() + $c->progresses->where('status', 'DONE')->count();
                        $totalProgress = $c->progresses->count();
                        $effectiveTkbm = $c->tkbm_option ?: $order->tkbm_option;
                    @endphp
                    <div class="bg-slate-50 hover:bg-blue-50/50 border border-slate-200 hover:border-blue-400 rounded-2xl p-5 transition shadow-sm hover:shadow flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 font-extrabold rounded-lg text-xs uppercase tracking-wide">
                                    {{ $c->container_size }} - {{ $c->container_type }}
                                </span>
                                @if($effectiveTkbm)
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-xs flex items-center gap-1 border border-amber-200">
                                        <i class="fa-solid fa-users-gear text-[10px]"></i> {{ $effectiveTkbm }}
                                    </span>
                                @endif
                                @if(!empty($c->additional_services))
                                    @foreach($c->additional_services as $as)
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-semibold rounded text-xs">
                                            +{{ $as }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>

                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Nomor Kontainer</div>
                                <div class="text-2xl font-black text-slate-800 tracking-tight">
                                    {{ $c->container_number ?? 'Tanpa Nomor' }}
                                </div>
                            </div>

                            @if($totalProgress > 0)
                                <div class="text-xs text-slate-600 font-medium">
                                    Progress Lapangan: <strong class="text-blue-700">{{ $completedProgressCount }} / {{ $totalProgress }} Selesai</strong>
                                </div>
                            @else
                                <div class="text-xs text-slate-400 italic">
                                    Belum ada aktivitas tiket supir khusus
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 sm:self-center">
                            <button type="button" onclick="openContainerEditModal({{ $c->id }}, '{{ $c->container_number }}', '{{ $c->tkbm_option }}')" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit Layanan Kontainer Ini</span>
                            </button>
                            <a href="{{ route('requests.containers.show', [$order->id, $c->id]) }}" class="px-5 py-2.5 bg-slate-900 hover:bg-blue-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition shadow-md whitespace-nowrap">
                                <span>Detail & SubTask</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
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
                            <div class="font-bold text-blue-700">
                                @if(!empty($sc->added_services))
                                    {{ implode(', ', $sc->added_services) }}
                                @else
                                    <span class="text-slate-400 font-normal">Tidak ada layanan baru</span>
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
                $hasHaulage = in_array('Haulage', $existingOrderServices);
                $hasLolo = in_array('LOLO', $existingOrderServices);
                $hasPenumpukan = in_array('Penumpukan', $existingOrderServices);
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
                            <span class="font-bold text-slate-800 text-sm">Haulage</span>
                        </div>
                        @if($hasHaulage)
                            <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                                <i class="fa-solid fa-check text-xs font-black"></i>
                            </div>
                            <input type="hidden" name="existing_services[]" value="Haulage">
                        @else
                            <label class="cursor-pointer">
                                <input type="checkbox" name="added_services[]" value="Haulage" class="w-5 h-5 text-blue-900 rounded border-slate-300 focus:ring-blue-600">
                            </label>
                        @endif
                    </div>

                    <!-- Dokumen Haulage -->
                    <div class="pt-2 border-t border-slate-300/60">
                        <div class="text-xs font-semibold text-slate-600 mb-1.5">Dokumen Haulage</div>
                        <input type="file" id="order_haulage_file" name="supporting_letter" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleFileSelected(this, 'order_haulage_label')">
                        <div onclick="triggerFileInput('order_haulage_file')" class="bg-white rounded-xl border border-slate-200 p-3.5 flex items-center justify-between cursor-pointer hover:border-blue-400 transition shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-file-arrow-up"></i>
                                </div>
                                <div>
                                    <div id="order_haulage_label" class="text-xs font-bold text-slate-800">Upload SP2 (PDF / JPG / PNG)</div>
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
                        <span class="font-bold text-slate-800 text-sm">Penumpukan</span>
                    </div>
                    @if($hasPenumpukan)
                        <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                            <i class="fa-solid fa-check text-xs font-black"></i>
                        </div>
                        <input type="hidden" name="existing_services[]" value="Penumpukan">
                    @else
                        <label class="cursor-pointer">
                            <input type="checkbox" name="added_services[]" value="Penumpukan" class="w-5 h-5 text-blue-900 rounded border-slate-300 focus:ring-blue-600">
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
                            <span class="font-bold text-slate-800 text-sm">Haulage</span>
                        </div>
                        <div class="w-6 h-6 bg-[#1C325B] text-white rounded-lg flex items-center justify-center shadow-sm cursor-not-allowed" title="Layanan sudah ada (Terkunci)">
                            <i class="fa-solid fa-check text-xs font-black"></i>
                        </div>
                    </div>

                    <!-- Dokumen Haulage -->
                    <div class="pt-2 border-t border-slate-300/60">
                        <div class="text-xs font-semibold text-slate-600 mb-1.5">Dokumen Haulage</div>
                        <input type="file" id="container_haulage_file" name="supporting_letter" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleFileSelected(this, 'container_haulage_label')">
                        <div onclick="triggerFileInput('container_haulage_file')" class="bg-white rounded-xl border border-slate-200 p-3.5 flex items-center justify-between cursor-pointer hover:border-blue-400 transition shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-file-arrow-up"></i>
                                </div>
                                <div>
                                    <div id="container_haulage_label" class="text-xs font-bold text-slate-800">Upload SP2 (PDF / JPG / PNG)</div>
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
                        <span class="font-bold text-slate-800 text-sm">Penumpukan</span>
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

<script>
    function openEditServicesModal() {
        document.getElementById('editServicesModal').classList.remove('hidden');
    }

    function closeEditServicesModal() {
        document.getElementById('editServicesModal').classList.add('hidden');
    }

    function openContainerEditModal(containerId, containerNumber, currentTkbm) {
        var actionUrl = "{{ url('requests/' . $order->id . '/containers') }}/" + containerId + "/update-services";
        document.getElementById('containerEditForm').action = actionUrl;
        document.getElementById('containerEditTitle').innerText = "Kontainer: " + (containerNumber || ("ID-" + containerId));
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
</script>
@endsection
