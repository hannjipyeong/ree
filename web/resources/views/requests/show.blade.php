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
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
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
            <div class="text-xs text-slate-400 font-bold uppercase">Tanggal Order</div>
            <div class="text-base font-bold text-slate-800 mt-1">{{ $order->tanggal_order->format('d F Y') }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Payload: {{ $order->payload_type }}</div>
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

        <div class="space-y-4">
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
                    @if($st->in_note || $st->out_note || $st->in_photo_path || $st->out_photo_path)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-200 text-xs">
                            @if($st->in_note || $st->in_photo_path)
                                <div class="p-3 bg-white rounded-xl border border-slate-200">
                                    <div class="font-bold text-blue-600 mb-1"><i class="fa-solid fa-right-to-bracket me-1"></i> Aksi IN (Masuk Gerbang)</div>
                                    <div class="text-slate-600">{{ $st->in_note ?? 'Tidak ada catatan' }}</div>
                                    @if($st->in_photo_path)
                                        <a href="{{ asset($st->in_photo_path) }}" target="_blank" class="mt-2 inline-block text-blue-600 font-semibold hover:underline">
                                            <i class="fa-solid fa-image me-1"></i> Lihat Foto Bukti IN
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if($st->out_note || $st->out_photo_path)
                                <div class="p-3 bg-white rounded-xl border border-slate-200">
                                    <div class="font-bold text-amber-600 mb-1"><i class="fa-solid fa-right-from-bracket me-1"></i> Aksi OUT (Keluar Gerbang)</div>
                                    <div class="text-slate-600">{{ $st->out_note ?? 'Tidak ada catatan' }}</div>
                                    @if($st->out_photo_path)
                                        <a href="{{ asset($st->out_photo_path) }}" target="_blank" class="mt-2 inline-block text-amber-600 font-semibold hover:underline">
                                            <i class="fa-solid fa-image me-1"></i> Lihat Foto Bukti OUT
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Admin Update Status Form -->
                    <form action="{{ route('subtasks.updateStatus', $st->id) }}" method="POST" class="pt-3 border-t border-slate-200 flex flex-wrap items-center gap-3">
                        @csrf
                        @method('PATCH')

                        <span class="text-xs font-bold text-slate-700">Update Status Manual (Admin):</span>
                        
                        <select name="status" class="py-1.5 px-3 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800">
                            <option value="Masuk" {{ $st->status == 'Masuk' ? 'selected' : '' }}>Masuk (Pending)</option>
                            <option value="In" {{ $st->status == 'In' ? 'selected' : '' }}>In (Progres IN)</option>
                            <option value="Out" {{ $st->status == 'Out' ? 'selected' : '' }}>Out (Progres OUT)</option>
                            <option value="Done" {{ $st->status == 'Done' ? 'selected' : '' }}>Done (Selesai)</option>
                        </select>

                        <input type="text" name="note" placeholder="Tambahkan catatan admin..." class="py-1.5 px-3 bg-white border border-slate-300 rounded-lg text-xs flex-1">

                        <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-900 transition">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
