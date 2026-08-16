@extends('layouts.app')

@section('title', 'Monitoring & CRUD Request')
@section('page_heading', 'Monitoring & CRUD Request (Order Mobile App)')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Filters -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('requests.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative min-w-[240px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Order / Nama PT..." 
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
            </div>

            <select name="source" onchange="this.form.submit()" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none">
                <option value="">-- Semua Source --</option>
                <option value="ALL IN" {{ request('source') == 'ALL IN' ? 'selected' : '' }}>ALL IN</option>
                <option value="Koperasi" {{ request('source') == 'Koperasi' ? 'selected' : '' }}>Koperasi</option>
                <option value="PBM Lain" {{ request('source') == 'PBM Lain' ? 'selected' : '' }}>PBM Lain</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-900 transition">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'source']))
                <a href="{{ route('requests.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">Reset</a>
            @endif
        </form>

        <!-- New Request Button -->
        <a href="{{ route('requests.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Buat Order Request Manual</span>
        </a>
    </div>

    <!-- Request Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">No. Order</th>
                        <th class="py-3.5 px-6">Source</th>
                        <th class="py-3.5 px-6">Nama PT & PBM</th>
                        <th class="py-3.5 px-6">Lokasi & Kegiatan</th>
                        <th class="py-3.5 px-6">Detail Muatan</th>
                        <th class="py-3.5 px-6">Status Tiket Supir</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $ord)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-blue-600">
                                {{ $ord->order_number }}
                                <div class="text-[11px] text-slate-400 font-normal mt-0.5">
                                    {{ $ord->tanggal_order->format('d M Y') }}
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $ord->source == 'ALL IN' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                                    {{ $ord->source == 'Koperasi' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $ord->source == 'PBM Lain' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}">
                                    {{ $ord->source }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">{{ $ord->nama_pt }}</div>
                                <div class="text-xs text-slate-400">PBM: {{ $ord->nama_pbm }} | {{ $ord->no_telp }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-700">{{ $ord->wilayah }} — {{ $ord->lokasi_fasilitas }}</div>
                                <div class="text-xs text-slate-400">Kegiatan: {{ $ord->jenis_kegiatan }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-medium">
                                    {{ $ord->payload_type }} ({{ $ord->containers->count() }} kontainer)
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col gap-1">
                                    @foreach($ord->subTasks as $st)
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="font-semibold text-slate-700 w-20">{{ $st->service_type }}:</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                                {{ $st->status == 'Masuk' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}
                                                {{ $st->status == 'In' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                                                {{ $st->status == 'Out' ? 'bg-amber-100 text-amber-700 border border-amber-200' : '' }}
                                                {{ $st->status == 'Done' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}">
                                                {{ $st->status }}
                                            </span>
                                            @if($st->supir)
                                                <span class="text-[10px] text-slate-400">({{ $st->supir->name }})</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right space-x-1">
                                <a href="{{ route('requests.show', $ord->id) }}" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                    <i class="fa-solid fa-eye me-1"></i> Detail
                                </a>

                                <form action="{{ route('requests.destroy', $ord->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus order ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-3"></i>
                                <div>Tidak ada data request ditemukan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection
