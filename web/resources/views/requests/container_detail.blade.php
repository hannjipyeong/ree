@extends('layouts.app')

@section('title', 'Detail Kontainer ' . ($container->container_number ?? 'No-ID'))
@section('page_heading', 'Detail Kontainer: ' . ($container->container_number ?? 'Tanpa Nomor'))

@section('content')
<div class="space-y-8">

    <!-- Breadcrumb Navigation -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500">
        <a href="{{ route('requests.index') }}" class="hover:text-slate-800 transition flex items-center gap-1.5">
            <i class="fa-solid fa-layer-group"></i> Daftar Request
        </a>
        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('requests.show', $order->id) }}" class="hover:text-slate-800 transition">
            Order #{{ $order->order_number }} ({{ $order->nama_pt }})
        </a>
        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-blue-600 font-bold">
            Kontainer {{ $container->container_number ?? 'ID-' . $container->id }}
        </span>
    </nav>

    <!-- Container Header Card -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 text-white rounded-3xl p-6 shadow-xl space-y-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="px-3 py-1 bg-blue-500/20 text-blue-300 font-extrabold rounded-lg text-xs border border-blue-400/30 uppercase tracking-wider">
                        {{ $container->container_type }} ({{ $container->container_size }})
                    </span>
                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 font-bold rounded-lg text-xs border border-emerald-400/30 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check text-[10px]"></i> Verified Container
                    </span>
                    @if($container->tkbm_option || $order->tkbm_option)
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-bold rounded-lg text-xs border border-amber-400/30 flex items-center gap-1.5">
                            <i class="fa-solid fa-users-gear text-[10px]"></i> TKBM: {{ $container->tkbm_option ?: $order->tkbm_option }}
                        </span>
                    @endif
                </div>
                <h2 class="text-3xl font-black tracking-tight text-white mt-2">
                    {{ $container->container_number ?? 'Tanpa Nomor Kontainer' }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="openContainerEditModal()" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-amber-500/30 transition">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Edit Layanan Kontainer Ini</span>
                </button>
                <a href="{{ route('requests.show', $order->id) }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-semibold backdrop-blur border border-white/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-white/10 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Customer / PT</span>
                <span class="font-bold text-white text-sm">{{ $order->nama_pt }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Lokasi Fasilitas</span>
                <span class="font-bold text-white text-sm">{{ $order->wilayah }} — {{ $order->lokasi_fasilitas }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Tanggal Order</span>
                <span class="font-bold text-white text-sm">{{ $order->tanggal_order->format('d M Y') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] font-bold uppercase">Opsi TKBM Khusus</span>
                <span class="font-bold text-amber-400 text-sm">{{ $container->tkbm_option ?: ($order->tkbm_option ?? 'Standard') }}</span>
            </div>
        </div>
    </div>

    <!-- Sub-Tasks & Container Progress Monitoring -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-blue-600"></i>
                Monitoring Tiket Layanan & Progress Lapangan Kontainer Ini
            </h3>
        </div>

        <div class="space-y-6">
            @forelse($order->subTasks as $st)
                @php
                    $cp = $container->progresses->where('sub_task_id', $st->id)->first();
                    $currentStatus = $cp ? $cp->status : $st->status;
                @endphp
                <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="font-extrabold text-slate-800 text-base">{{ $st->task_number }}</span>
                                <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-slate-800 text-white">
                                    {{ $st->service_type }}
                                </span>
                            </div>
                            @if($st->service_type == 'TKBM')
                                @php
                                    $tkbmType = $container->tkbm_option ?: ($order->tkbm_option ?? null);
                                @endphp
                                @if($tkbmType)
                                    <div class="mt-1.5">
                                        @if($tkbmType == 'Man Power + Forklift')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                                <i class="fa-solid fa-forklift text-[10px]"></i> Man Power + Fork Lift
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                                                <i class="fa-solid fa-people-carry-box text-[10px]"></i> Man Power
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endif
                            <div class="text-xs text-slate-500 mt-1">
                                Driver / Operator: 
                                <strong class="text-slate-700">{{ $st->supir ? $st->supir->name : 'Belum ditugaskan' }}</strong>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase shadow-sm
                                {{ $currentStatus == 'Masuk' ? 'bg-slate-200 text-slate-800' : '' }}
                                {{ $currentStatus == 'IN' || $currentStatus == 'In' ? 'bg-blue-600 text-white' : '' }}
                                {{ $currentStatus == 'OUT' || $currentStatus == 'Out' ? 'bg-amber-500 text-white' : '' }}
                                {{ $currentStatus == 'DONE' || $currentStatus == 'Done' ? 'bg-emerald-600 text-white' : '' }}">
                                Status Lapangan: {{ $currentStatus }}
                            </span>
                        </div>
                    </div>

                    <!-- Notes & Photo Proofs per Container -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-200 text-xs">
                        
                        <!-- Bukti IN -->
                        <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                            <div class="font-bold text-blue-600 flex items-center justify-between">
                                <span><i class="fa-solid fa-right-to-bracket me-1"></i> Progress IN (Masuk Gerbang)</span>
                                <div class="flex items-center gap-1.5">
                                    @if($cp && $cp->in_time)
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            <i class="fa-regular fa-clock me-0.5"></i>{{ $cp->in_time->format('d M Y, H:i') }}
                                        </span>
                                    @endif
                                    @if($cp && $cp->in_photo_path)
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-semibold border border-blue-200">Ada Bukti Foto</span>
                                    @elseif($st->in_photo_path)
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-semibold border border-blue-200">Foto Order</span>
                                    @endif
                                </div>
                            </div>
                            
                            @php
                                $inPath = ($cp && $cp->in_photo_path) ? $cp->in_photo_path : $st->in_photo_path;
                            @endphp
                            @if($inPath)
                                @php
                                    $inUrl = Str::startsWith($inPath, ['http://', 'https://']) ? $inPath : asset($inPath);
                                @endphp
                                <div class="pt-2 space-y-1">
                                    <button type="button" onclick="openPhotoModal('{{ $inUrl }}', 'Foto Bukti IN Kontainer - {{ $container->container_number }}')" class="w-full py-2 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-lg transition flex items-center justify-center gap-2 border border-blue-200">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Lihat Foto Bukti IN</span>
                                    </button>
                                    @if($cp)
                                        <div class="text-[10px] text-slate-400 text-center">
                                            <i class="fa-regular fa-clock me-0.5"></i>{{ $cp->updated_at->format('d M Y, H:i') }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-[11px] text-slate-400 italic">Belum ada foto bukti IN</div>
                            @endif
                        </div>

                        <!-- Bukti OUT -->
                        <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                            <div class="font-bold text-amber-600 flex items-center justify-between">
                                <span><i class="fa-solid fa-right-from-bracket me-1"></i> Progress OUT (Keluar Gerbang)</span>
                                <div class="flex items-center gap-1.5">
                                    @if($cp && $cp->out_time)
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            <i class="fa-regular fa-clock me-0.5"></i>{{ $cp->out_time->format('d M Y, H:i') }}
                                        </span>
                                    @endif
                                    @if($cp && $cp->out_photo_path)
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] font-semibold border border-amber-200">Ada Bukti Foto</span>
                                    @elseif($st->out_photo_path)
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] font-semibold border border-amber-200">Foto Order</span>
                                    @endif
                                </div>
                            </div>

                            @php
                                $outPath = ($cp && $cp->out_photo_path) ? $cp->out_photo_path : $st->out_photo_path;
                            @endphp
                            @if($outPath)
                                @php
                                    $outUrl = Str::startsWith($outPath, ['http://', 'https://']) ? $outPath : asset($outPath);
                                @endphp
                                <div class="pt-2 space-y-1">
                                    <button type="button" onclick="openPhotoModal('{{ $outUrl }}', 'Foto Bukti OUT Kontainer - {{ $container->container_number }}')" class="w-full py-2 px-3 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold rounded-lg transition flex items-center justify-center gap-2 border border-amber-200">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Lihat Foto Bukti OUT</span>
                                    </button>
                                    @if($cp)
                                        <div class="text-[10px] text-slate-400 text-center">
                                            <i class="fa-regular fa-clock me-0.5"></i>{{ $cp->updated_at->format('d M Y, H:i') }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-[11px] text-slate-400 italic">Belum ada foto bukti OUT</div>
                            @endif
                        </div>

                    </div>

                    <!-- Form Update Status Admin -->
                    <form action="{{ route('subtasks.updateStatus', $st->id) }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-slate-200 space-y-3">
                        @csrf
                        @method('PATCH')

                        <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                            Update Status Manual (Admin):
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Tiket</label>
                                <select name="status" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                    <option value="Masuk" {{ $currentStatus == 'Masuk' ? 'selected' : '' }}>Masuk (Pending)</option>
                                    <option value="In" {{ $currentStatus == 'In' || $currentStatus == 'IN' ? 'selected' : '' }}>In (Progres IN)</option>
                                    <option value="Out" {{ $currentStatus == 'Out' || $currentStatus == 'OUT' ? 'selected' : '' }}>Out (Progres OUT)</option>
                                    <option value="Done" {{ $currentStatus == 'Done' || $currentStatus == 'DONE' ? 'selected' : '' }}>Done (Selesai)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Catatan</label>
                                <input type="text" name="note" placeholder="Catatan..." class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Upload Foto Bukti</label>
                                <input type="file" name="photo" accept="image/*" class="w-full py-1 px-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-600 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700">
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold shadow transition">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @empty
                <div class="p-8 bg-slate-50 rounded-2xl border border-dashed border-slate-300 text-center text-slate-400 text-xs">
                    Belum ada tiket SubTask layanan terdaftar.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal Popup Edit Layanan Khusus Kontainer -->
<div id="containerEditModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl space-y-0">
        <div class="p-5 bg-[#1C325B] text-white flex items-center justify-between">
            <div>
                <h4 class="font-bold text-base">Edit Layanan Khusus Kontainer</h4>
                <p class="text-xs text-amber-300 mt-0.5">Kontainer: {{ $container->container_number ?? ('#' . $container->id) }}</p>
            </div>
            <button type="button" onclick="closeContainerEditModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('requests.containers.updateServices', [$order->id, $container->id]) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5 max-h-[82vh] overflow-y-auto">
            @csrf

            @php
                $containerServices = $container->additional_services ?: [];
                $orderServices = $order->subTasks->pluck('service_type')->toArray();
                $hasHaulage = in_array('Haulage', $containerServices) || in_array('Haulage', $orderServices);
                $hasLolo = in_array('LOLO', $containerServices) || in_array('LOLO', $orderServices);
                $hasPenumpukan = in_array('Penumpukan', $containerServices) || in_array('Penumpukan', $orderServices);
                $hasTkbm = in_array('TKBM', $containerServices) || in_array('TKBM', $orderServices) || !empty($container->tkbm_option) || !empty($order->tkbm_option);
                $currentTkbm = $container->tkbm_option ?: ($order->tkbm_option ?? 'Man Power');
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
                        <input type="hidden" id="container_tkbm_option_input" name="tkbm_option" value="{{ $currentTkbm }}">
                        
                        <!-- Radio Option 1: Man Power -->
                        <div onclick="selectTkbmOption('container', 'Man Power')" data-option="Man Power" class="container-tkbm-card p-3 rounded-xl border flex items-center gap-3 cursor-pointer transition shadow-sm {{ $currentTkbm == 'Man Power' ? 'border-2 border-[#1C325B] bg-slate-200/90' : 'border-slate-200 bg-white' }}">
                            <div class="tkbm-dot-border w-5 h-5 rounded-full border-2 {{ $currentTkbm == 'Man Power' ? 'border-[#1C325B]' : 'border-slate-300 bg-white' }} flex items-center justify-center">
                                <div class="tkbm-dot-inner w-2.5 h-2.5 rounded-full bg-[#1C325B]" style="display: {{ $currentTkbm == 'Man Power' ? 'block' : 'none' }}"></div>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Man Power</span>
                        </div>

                        <!-- Radio Option 2: Man Power + Forklift -->
                        <div onclick="selectTkbmOption('container', 'Man Power + Forklift')" data-option="Man Power + Forklift" class="container-tkbm-card p-3 rounded-xl border flex items-center gap-3 cursor-pointer transition shadow-sm {{ $currentTkbm == 'Man Power + Forklift' ? 'border-2 border-[#1C325B] bg-slate-200/90' : 'border-slate-200 bg-white' }}">
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
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama / Keterangan Surat Pendukung Lapangan</label>
                    <input type="text" name="document_name" placeholder="misal: Surat Tambahan TKBM Kontainer {{ $container->container_number }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none">
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
    function openContainerEditModal() {
        document.getElementById('containerEditModal').classList.remove('hidden');
    }

    function closeContainerEditModal() {
        document.getElementById('containerEditModal').classList.add('hidden');
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
