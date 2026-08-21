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
                    <div id="subtask-display-{{ $st->id }}" class="space-y-4">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-3">
                                    <span class="font-extrabold text-slate-800 text-base">{{ $st->task_number }}</span>
                                    <span class="px-2.5 py-0.5 rounded text-xs font-extrabold uppercase border
                                        {{ $st->service_type == 'Haulage' ? 'bg-purple-100 text-purple-800 border-purple-200' : '' }}
                                        {{ $st->service_type == 'LOLO' ? 'bg-sky-100 text-sky-800 border-sky-200' : '' }}
                                        {{ $st->service_type == 'Penumpukan' ? 'bg-amber-100 text-amber-800 border-amber-200' : '' }}
                                        {{ $st->service_type == 'TKBM' ? 'bg-teal-100 text-teal-800 border-teal-200' : '' }}
                                        {{ !in_array($st->service_type, ['Haulage', 'LOLO', 'Penumpukan', 'TKBM']) ? 'bg-slate-800 text-white' : '' }}">
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
                                    Pelaksana Lapangan / Driver: 
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

                        <!-- Notes & Photo Proofs per Container (Multi-Photo Gallery) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3 border-t border-slate-200 text-xs">
                            
                            <!-- Bukti IN (Multi-Photo) -->
                            <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                                <div class="font-bold text-blue-600 flex items-center justify-between">
                                    <span><i class="fa-solid fa-right-to-bracket me-1"></i> Foto IN</span>
                                    @if($cp && $cp->in_time)
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            <i class="fa-regular fa-clock me-0.5"></i>{{ $cp->in_time->format('d M Y, H:i') }}
                                        </span>
                                    @endif
                                </div>
                                @php
                                    $allInPhotos = $cp ? $cp->all_in_photos : $st->all_in_photos;
                                @endphp
                                @if(!empty($allInPhotos))
                                    <div class="grid grid-cols-3 gap-1.5 pt-1">
                                        @foreach($allInPhotos as $inPhoto)
                                            @php $inPhotoUrl = Str::startsWith($inPhoto, ['http://', 'https://']) ? $inPhoto : asset($inPhoto); @endphp
                                            <button type="button" onclick="openPhotoModal('{{ $inPhotoUrl }}', 'Foto IN')" class="aspect-square rounded-lg overflow-hidden border border-slate-200 hover:border-blue-400 transition cursor-pointer">
                                                <img src="{{ $inPhotoUrl }}" alt="Foto IN" class="w-full h-full object-cover">
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-[11px] text-slate-400 italic">Belum ada foto IN</div>
                                @endif
                            </div>

                            <!-- Bukti OUT (Multi-Photo) -->
                            <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                                <div class="font-bold text-amber-600 flex items-center justify-between">
                                    <span><i class="fa-solid fa-right-from-bracket me-1"></i> Foto OUT</span>
                                    @if($cp && $cp->out_time)
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            <i class="fa-regular fa-clock me-0.5"></i>{{ $cp->out_time->format('d M Y, H:i') }}
                                        </span>
                                    @endif
                                </div>
                                @php
                                    $allOutPhotos = $cp ? $cp->all_out_photos : $st->all_out_photos;
                                @endphp
                                @if(!empty($allOutPhotos))
                                    <div class="grid grid-cols-3 gap-1.5 pt-1">
                                        @foreach($allOutPhotos as $outPhoto)
                                            @php $outPhotoUrl = Str::startsWith($outPhoto, ['http://', 'https://']) ? $outPhoto : asset($outPhoto); @endphp
                                            <button type="button" onclick="openPhotoModal('{{ $outPhotoUrl }}', 'Foto OUT')" class="aspect-square rounded-lg overflow-hidden border border-slate-200 hover:border-amber-400 transition cursor-pointer">
                                                <img src="{{ $outPhotoUrl }}" alt="Foto OUT" class="w-full h-full object-cover">
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-[11px] text-slate-400 italic">Belum ada foto OUT</div>
                                @endif
                            </div>

                            <!-- Bukti DONE (Multi-Photo) -->
                            <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                                <div class="font-bold text-emerald-600 flex items-center justify-between">
                                    <span><i class="fa-solid fa-circle-check me-1"></i> Foto DONE</span>
                                    @if($cp && $cp->done_time)
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            <i class="fa-regular fa-clock me-0.5"></i>{{ $cp->done_time->format('d M Y, H:i') }}
                                        </span>
                                    @endif
                                </div>
                                @php
                                    $allDonePhotos = $cp ? $cp->all_done_photos : $st->all_done_photos;
                                @endphp
                                @if(!empty($allDonePhotos))
                                    <div class="grid grid-cols-3 gap-1.5 pt-1">
                                        @foreach($allDonePhotos as $donePhoto)
                                            @php $donePhotoUrl = Str::startsWith($donePhoto, ['http://', 'https://']) ? $donePhoto : asset($donePhoto); @endphp
                                            <button type="button" onclick="openPhotoModal('{{ $donePhotoUrl }}', 'Foto DONE')" class="aspect-square rounded-lg overflow-hidden border border-slate-200 hover:border-emerald-400 transition cursor-pointer">
                                                <img src="{{ $donePhotoUrl }}" alt="Foto DONE" class="w-full h-full object-cover">
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-[11px] text-slate-400 italic">Belum ada foto DONE</div>
                                @endif
                                @if($cp && $cp->done_note)
                                    <div class="mt-1 p-2 bg-emerald-50 rounded-lg text-[11px] text-emerald-800 border border-emerald-200">
                                        <strong>Keterangan:</strong> {{ $cp->done_note }}
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    <!-- Form Update Status Admin (AJAX Real-Time) -->
                    <form id="statusForm-{{ $st->id }}" action="{{ route('subtasks.updateStatus', $st->id) }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-slate-200 space-y-3" onsubmit="return submitStatusAjax(event, {{ $st->id }})">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="container_id" value="{{ $container->id }}">

                        <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                            Update Status Manual (Admin):
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Tiket</label>
                                <select name="status" id="statusSelect-{{ $st->id }}" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
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
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Upload Foto Bukti (Multi)</label>
                                <input type="file" name="photos[]" accept="image/*" multiple class="w-full py-1 px-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-600 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700">
                                <p class="text-[9px] text-slate-400 mt-0.5">Bisa pilih banyak foto sekaligus</p>
                            </div>

                            @if($st->service_type === 'Haulage')
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">SP3KK (Khusus Haulage)</label>
                                    <input type="file" name="sp3kk_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full py-1 px-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-600 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-emerald-50 file:text-emerald-700">
                                    @if($container->sp3kk_file_path)
                                        <a href="{{ $container->sp3kk_file_url }}" target="_blank" class="text-[9px] text-emerald-600 font-bold mt-1 inline-block hover:underline"><i class="fa-solid fa-eye"></i> Lihat SP3KK Terupload</a>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-end {{ $st->service_type === 'Haulage' ? 'md:col-span-4' : '' }}">
                                <button type="submit" id="submitBtn-{{ $st->id }}" class="w-full py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold shadow transition flex items-center justify-center gap-2">
                                    <span class="btn-text">Simpan Perubahan</span>
                                    <span class="btn-spinner hidden"><i class="fa-solid fa-spinner fa-spin"></i></span>
                                </button>
                            </div>
                        </div>

                        <!-- Real-time feedback message -->
                        <div id="statusMsg-{{ $st->id }}" class="hidden text-xs font-bold px-3 py-2 rounded-xl flex items-center gap-2 transition-all duration-300"></div>
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
                    <div class="pt-2 border-t border-slate-300/60 flex flex-col gap-3">
                        <div class="text-xs font-semibold text-slate-600">Dokumen Haulage</div>
                        
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

                        <input type="file" id="container_sp3kk_file" name="sp3kk_file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleFileSelected(this, 'container_sp3kk_label')">
                        <div onclick="triggerFileInput('container_sp3kk_file')" class="bg-white rounded-xl border border-slate-200 p-3.5 flex items-center justify-between cursor-pointer hover:border-emerald-400 transition shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <div>
                                    <div id="container_sp3kk_label" class="text-xs font-bold text-slate-800">Upload SP3KK (PDF / JPG / PNG)</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Opsional khusus Haulage</div>
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
                                <input type="checkbox" name="added_services[]" value="Asuransi" id="cd_asuransi_cb" class="w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" onchange="document.getElementById('cd_asuransi_value_wrap').classList.toggle('hidden', !this.checked)">
                            </label>
                        @endif
                    </div>

                    <div id="cd_asuransi_value_wrap" class="{{ $order->has_asuransi ? '' : 'hidden' }} pt-2 border-t border-emerald-200/60">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nilai Pertanggungan (Rp)</label>
                        <input type="number" name="asuransi_value" value="{{ $order->asuransi_value }}" placeholder="misal: 50000000" min="0" step="1000" class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
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

    // ===== AJAX STATUS UPDATE & REAL-TIME POLLING =====
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
        msgDiv.className = 'hidden text-xs font-bold px-3 py-2 rounded-xl flex items-center gap-2 transition-all duration-300';

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
            form.querySelector('input[name="note"]').value = '';
            form.querySelector('input[type="file"]').value = '';

            // Update UI immediately
            pollContainerUpdates();

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
            msgDiv.textContent = err.message || 'Terjadi kesalahan saat menyimpan perubahan.';
            msgDiv.classList.remove('hidden');
            msgDiv.classList.add('bg-rose-50', 'text-rose-800', 'border', 'border-rose-200');
        })
        .finally(() => {
            // Hide spinner, enable button
            btnText.style.display = 'block';
            btnSpinner.classList.add('hidden');
            submitBtn.disabled = false;
        });

        return false;
    }

    function pollContainerUpdates() {
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

            // Update select values if they are not currently focused/active
            document.querySelectorAll('[id^="statusSelect-"]').forEach(selectEl => {
                const id = selectEl.id;
                const newSelectEl = doc.getElementById(id);
                if (newSelectEl && document.activeElement !== selectEl) {
                    selectEl.value = newSelectEl.value;
                }
            });
        })
        .catch(err => console.error("Error polling container updates:", err));
    }

    // Set polling every 3 seconds
    setInterval(pollContainerUpdates, 3000);

    document.addEventListener('DOMContentLoaded', function() {
        @if($container->sp3kk_file_path)
            document.getElementById('container_sp3kk_label').innerText = "SP3KK.{{ pathinfo($container->sp3kk_file_path, PATHINFO_EXTENSION) }} (Terupload)";
            let sp3kkView = document.getElementById('container_sp3kk_view');
            if (sp3kkView) {
                sp3kkView.href = "{{ $container->sp3kk_file_url }}";
                sp3kkView.classList.remove('hidden');
                sp3kkView.classList.add('inline-flex');
            }
        @endif
    });
</script>
@endsection
