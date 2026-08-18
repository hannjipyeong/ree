@extends('layouts.app')

@section('title', 'Detail Request ' . $order->order_number)
@section('page_heading', 'Detail Order Request: ' . $order->order_number)

@section('content')
<div class="space-y-8">

    <div class="flex items-center justify-between">
        <a href="{{ route('requests.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Monitoring
        </a>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-purple-50 text-purple-700 font-semibold rounded-full text-xs border border-purple-200">
                Source: {{ $order->source }}
            </span>
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
            <div class="text-xs text-slate-400 font-bold uppercase">Layanan Tambahan</div>
            <div class="text-base font-bold text-slate-800 mt-1">
                @if($order->has_asuransi)
                    <span class="text-emerald-600 flex items-center gap-1 text-sm">
                        <i class="fa-solid fa-shield-halved"></i> Asuransi Cargo Aktif
                    </span>
                    @if($order->asuransi_value)
                        <div class="text-xs text-slate-500 font-normal">Pertanggungan: Rp {{ number_format($order->asuransi_value, 0, ',', '.') }}</div>
                    @endif
                @else
                    <span class="text-slate-400 font-normal text-sm">Tanpa Asuransi</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Container List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
            <i class="fa-solid fa-boxes-stacked text-blue-600"></i>
            Daftar Kontainer ({{ $order->containers->count() }})
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($order->containers as $c)
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-800 text-sm">{{ $c->container_number ?? 'Tanpa Nomor' }}</div>
                        <div class="text-xs text-slate-500">{{ $c->container_type }} ({{ $c->container_size }})</div>
                    </div>
                    <span class="px-2.5 py-1 bg-blue-100 text-blue-700 font-semibold rounded text-xs">
                        Verified
                    </span>
                </div>
            @empty
                <div class="col-span-2 text-xs text-slate-400">Tidak ada detail kontainer terdaftar.</div>
            @endforelse
        </div>
    </div>

    <!-- Sub-Task Supir Monitoring & Action -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
            <i class="fa-solid fa-list-check text-blue-600"></i>
            Monitoring Tiket Layanan Supir (Diproses dari Mobile App / Web)
        </h3>

        <div class="space-y-6">
            @foreach($order->subTasks as $st)
                <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="font-extrabold text-slate-800 text-base">{{ $st->task_number }}</span>
                                <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-slate-800 text-white">
                                    {{ $st->service_type }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                Driver Penanggungjawab: 
                                <strong class="text-slate-700">{{ $st->supir ? $st->supir->name : 'Belum ditugaskan' }}</strong>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold uppercase shadow-sm
                                {{ $st->status == 'Masuk' ? 'bg-slate-200 text-slate-800' : '' }}
                                {{ $st->status == 'In' ? 'bg-blue-600 text-white' : '' }}
                                {{ $st->status == 'Out' ? 'bg-amber-500 text-white' : '' }}
                                {{ $st->status == 'Done' ? 'bg-emerald-600 text-white' : '' }}">
                                Status: {{ $st->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Notes & Photo Proofs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-200 text-xs">
                        
                        <!-- Bukti IN -->
                        <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                            <div class="font-bold text-blue-600 flex items-center justify-between">
                                <span><i class="fa-solid fa-right-to-bracket me-1"></i> Aksi IN (Masuk Gerbang)</span>
                                @if($st->in_photo_path)
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-semibold border border-blue-200">Ada Bukti Foto</span>
                                @endif
                            </div>
                            <div class="text-slate-600 font-medium">{{ $st->in_note ?? 'Belum ada catatan IN' }}</div>
                            
                            @if($st->in_photo_path)
                                @php
                                    $inUrl = Str::startsWith($st->in_photo_path, ['http://', 'https://']) ? $st->in_photo_path : asset($st->in_photo_path);
                                @endphp
                                <div class="pt-2">
                                    <button type="button" onclick="openPhotoModal('{{ $inUrl }}', 'Foto Bukti IN - {{ $st->task_number }}')" class="w-full py-2 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-lg transition flex items-center justify-center gap-2 border border-blue-200">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Lihat Foto Bukti IN</span>
                                    </button>
                                </div>
                            @else
                                <div class="text-[11px] text-slate-400 italic">Belum ada foto bukti IN</div>
                            @endif
                        </div>

                        <!-- Bukti OUT -->
                        <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                            <div class="font-bold text-amber-600 flex items-center justify-between">
                                <span><i class="fa-solid fa-right-from-bracket me-1"></i> Aksi OUT (Keluar Gerbang)</span>
                                @if($st->out_photo_path)
                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] font-semibold border border-amber-200">Ada Bukti Foto</span>
                                @endif
                            </div>
                            <div class="text-slate-600 font-medium">{{ $st->out_note ?? 'Belum ada catatan OUT' }}</div>

                            @if($st->out_photo_path)
                                @php
                                    $outUrl = Str::startsWith($st->out_photo_path, ['http://', 'https://']) ? $st->out_photo_path : asset($st->out_photo_path);
                                @endphp
                                <div class="pt-2">
                                    <button type="button" onclick="openPhotoModal('{{ $outUrl }}', 'Foto Bukti OUT - {{ $st->task_number }}')" class="w-full py-2 px-3 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold rounded-lg transition flex items-center justify-center gap-2 border border-amber-200">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Lihat Foto Bukti OUT</span>
                                    </button>
                                </div>
                            @else
                                <div class="text-[11px] text-slate-400 italic">Belum ada foto bukti OUT</div>
                            @endif
                        </div>

                    </div>

                    <!-- Admin Update Status & Upload Form -->
                    <form action="{{ route('subtasks.updateStatus', $st->id) }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-slate-200 space-y-3">
                        @csrf
                        @method('PATCH')

                        <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                            Update Status & Unggah Foto Bukti Manual (Admin):
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Tiket</label>
                                <select name="status" class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                    <option value="Masuk" {{ $st->status == 'Masuk' ? 'selected' : '' }}>Masuk (Pending)</option>
                                    <option value="In" {{ $st->status == 'In' ? 'selected' : '' }}>In (Progres IN)</option>
                                    <option value="Out" {{ $st->status == 'Out' ? 'selected' : '' }}>Out (Progres OUT)</option>
                                    <option value="Done" {{ $st->status == 'Done' ? 'selected' : '' }}>Done (Selesai)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Catatan</label>
                                <input type="text" name="note" placeholder="Catatan pergerakan..." class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
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
            @endforeach
        </div>
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
</script>
@endsection
