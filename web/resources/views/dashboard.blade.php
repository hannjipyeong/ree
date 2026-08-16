@extends('layouts.app')

@section('title', 'Dashboard Overview')
@section('page_heading', 'Dashboard Overview')

@section('content')
<div class="space-y-8">

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Orders -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Order Request</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mt-2">{{ $totalOrders }}</h3>
                <p class="text-xs text-blue-600 font-medium mt-1">
                    <i class="fa-solid fa-arrow-trend-up me-1"></i> Terhubung dengan Mobile App
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <!-- Total SubTasks -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Tiket Supir</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mt-2">{{ $totalSubTasks }}</h3>
                <p class="text-xs text-amber-600 font-medium mt-1">
                    <i class="fa-solid fa-clock me-1"></i> {{ $subTaskStats['masuk'] + $subTaskStats['in'] }} Tugas Berjalan
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>

        <!-- Total Customer -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Akun Customer</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mt-2">{{ $totalCustomers }}</h3>
                <p class="text-xs text-slate-500 font-medium mt-1">Perusahaan Pemesan</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-building-user"></i>
            </div>
        </div>

        <!-- Total Supir -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Akun Supir Active</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mt-2">{{ $totalSupir }}</h3>
                <p class="text-xs text-purple-600 font-medium mt-1">Driver Operasional</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-truck-front"></i>
            </div>
        </div>

    </div>

    <!-- Status Breakdown Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="bg-slate-800 text-white p-4 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-slate-400"></span>
                <span class="text-sm font-medium">Tiket Status 'Masuk'</span>
            </div>
            <span class="font-bold text-lg">{{ $subTaskStats['masuk'] }}</span>
        </div>
        <div class="bg-blue-600 text-white p-4 rounded-xl flex items-center justify-between shadow-lg shadow-blue-600/20">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-blue-200 animate-ping"></span>
                <span class="text-sm font-medium">Tiket Status 'IN'</span>
            </div>
            <span class="font-bold text-lg">{{ $subTaskStats['in'] }}</span>
        </div>
        <div class="bg-amber-500 text-white p-4 rounded-xl flex items-center justify-between shadow-lg shadow-amber-500/20">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-amber-200"></span>
                <span class="text-sm font-medium">Tiket Status 'OUT'</span>
            </div>
            <span class="font-bold text-lg">{{ $subTaskStats['out'] }}</span>
        </div>
        <div class="bg-emerald-600 text-white p-4 rounded-xl flex items-center justify-between shadow-lg shadow-emerald-600/20">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-emerald-200"></span>
                <span class="text-sm font-medium">Tiket Status 'Done'</span>
            </div>
            <span class="font-bold text-lg">{{ $subTaskStats['done'] }}</span>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Order Terbaru (Terhubung App)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Order yang di-submit dari aplikasi mobile secara real-time</p>
            </div>
            <a href="{{ route('requests.index') }}" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs rounded-xl transition">
                Lihat Semua Order <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">No. Order</th>
                        <th class="py-3.5 px-6">Customer / PT</th>
                        <th class="py-3.5 px-6">Source</th>
                        <th class="py-3.5 px-6">Wilayah & Fasilitas</th>
                        <th class="py-3.5 px-6">Tiket Task Supir</th>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $ord)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-semibold text-blue-600">
                                {{ $ord->order_number }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">{{ $ord->nama_pt }}</div>
                                <div class="text-xs text-slate-400">PBM: {{ $ord->nama_pbm }}</div>
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
                                <div class="text-slate-800 font-medium">{{ $ord->wilayah }}</div>
                                <div class="text-xs text-slate-400">{{ $ord->lokasi_fasilitas }} ({{ $ord->jenis_kegiatan }})</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($ord->subTasks as $st)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                            {{ $st->status == 'Masuk' ? 'bg-slate-100 text-slate-700' : '' }}
                                            {{ $st->status == 'In' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $st->status == 'Out' ? 'bg-amber-100 text-amber-700' : '' }}
                                            {{ $st->status == 'Done' ? 'bg-emerald-100 text-emerald-700' : '' }}">
                                            {{ $st->service_type }}: {{ $st->status }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500">
                                {{ $ord->tanggal_order->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('requests.show', $ord->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada order request yang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
