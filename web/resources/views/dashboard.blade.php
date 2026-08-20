@extends('layouts.app')

@section('title', 'Dashboard Overview')
@section('page_heading', 'Dashboard Overview')

@section('content')
<div class="space-y-6">

    @if(!empty($adminSource))
        <!-- Source Scope Banner -->
        <div class="p-5 rounded-2xl shadow-sm border flex flex-col sm:flex-row sm:items-center justify-between gap-4
            {{ $adminSource === 'ALL IN' ? 'bg-gradient-to-r from-purple-900 via-purple-800 to-indigo-900 text-purple-100 border-purple-700' : '' }}
            {{ $adminSource === 'Koperasi' ? 'bg-gradient-to-r from-amber-900 via-amber-800 to-orange-900 text-amber-100 border-amber-700' : '' }}
            {{ $adminSource === 'PBM Lain' ? 'bg-gradient-to-r from-blue-900 via-blue-800 to-slate-900 text-blue-100 border-blue-700' : '' }}">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center font-bold text-white text-xl shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-white flex items-center gap-2">
                        Portal Operasional: Modul {{ $adminSource }}
                        <span class="px-2.5 py-0.5 bg-white/20 rounded-full text-[10px] font-semibold uppercase tracking-wider">Terkunci</span>
                    </h4>
                    <p class="text-xs text-white/80 mt-0.5">Semua data metrik, tiket pelaksana lapangan, dan daftar order di halaman ini dikhususkan untuk source <strong>{{ $adminSource }}</strong>.</p>
                </div>
            </div>
            <a href="{{ route('requests.create') }}" class="px-4 py-2.5 bg-white text-slate-900 hover:bg-slate-100 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition shadow shrink-0">
                <i class="fa-solid fa-plus text-blue-600"></i> Buat Order {{ $adminSource }}
            </a>
        </div>
    @endif

    <!-- KPI Metric Cards Grid -->
    <div id="kpi-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
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
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Tiket Pelaksana Lapangan</p>
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

        <!-- Total Pelaksana Lapangan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Akun Pelaksana Lapangan Active</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mt-2">{{ $totalSupir }}</h3>
                <p class="text-xs text-purple-600 font-medium mt-1">Petugas / Driver Operasional</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-truck-front"></i>
            </div>
        </div>

    </div>

    <!-- Status Breakdown Cards (clickable filter) -->
    <div id="status-breakdown" class="grid grid-cols-1 lg:grid-cols-4 gap-4">

        {{-- Masuk --}}
        <a href="{{ $activeStatus == 'Masuk' ? route('dashboard') : route('dashboard', array_merge(request()->except('tiket_status'), ['tiket_status' => 'Masuk'])) }}"
           class="group p-4 rounded-xl flex items-center justify-between transition-all duration-200
                  bg-slate-800 text-white shadow
                  {{ $activeStatus && $activeStatus != 'Masuk' ? 'opacity-40 grayscale' : '' }}
                  {{ $activeStatus == 'Masuk' ? 'ring-2 ring-white/60 scale-[1.02]' : 'hover:bg-slate-700' }}">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-slate-400 {{ $activeStatus == 'Masuk' ? 'animate-pulse' : '' }}"></span>
                <span class="text-sm font-medium">Status 'Masuk'</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-bold text-lg">{{ $subTaskStats['masuk'] }}</span>
                @if($activeStatus == 'Masuk')
                    <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </span>
                @endif
            </div>
        </a>

        {{-- IN --}}
        <a href="{{ $activeStatus == 'In' ? route('dashboard') : route('dashboard', array_merge(request()->except('tiket_status'), ['tiket_status' => 'In'])) }}"
           class="group p-4 rounded-xl flex items-center justify-between transition-all duration-200
                  bg-blue-600 text-white shadow-lg shadow-blue-600/20
                  {{ $activeStatus && $activeStatus != 'In' ? 'opacity-40 grayscale' : '' }}
                  {{ $activeStatus == 'In' ? 'ring-2 ring-white/60 scale-[1.02]' : 'hover:bg-blue-500' }}">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-blue-200 {{ $activeStatus == 'In' ? 'animate-ping' : '' }}"></span>
                <span class="text-sm font-medium">Status 'IN'</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-bold text-lg">{{ $subTaskStats['in'] }}</span>
                @if($activeStatus == 'In')
                    <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </span>
                @endif
            </div>
        </a>

        {{-- OUT --}}
        <a href="{{ $activeStatus == 'Out' ? route('dashboard') : route('dashboard', array_merge(request()->except('tiket_status'), ['tiket_status' => 'Out'])) }}"
           class="group p-4 rounded-xl flex items-center justify-between transition-all duration-200
                  bg-amber-500 text-white shadow-lg shadow-amber-500/20
                  {{ $activeStatus && $activeStatus != 'Out' ? 'opacity-40 grayscale' : '' }}
                  {{ $activeStatus == 'Out' ? 'ring-2 ring-white/60 scale-[1.02]' : 'hover:bg-amber-400' }}">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-amber-200"></span>
                <span class="text-sm font-medium">Status 'OUT'</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-bold text-lg">{{ $subTaskStats['out'] }}</span>
                @if($activeStatus == 'Out')
                    <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </span>
                @endif
            </div>
        </a>

        {{-- Done --}}
        <a href="{{ $activeStatus == 'Done' ? route('dashboard') : route('dashboard', array_merge(request()->except('tiket_status'), ['tiket_status' => 'Done'])) }}"
           class="group p-4 rounded-xl flex items-center justify-between transition-all duration-200
                  bg-emerald-600 text-white shadow-lg shadow-emerald-600/20
                  {{ $activeStatus && $activeStatus != 'Done' ? 'opacity-40 grayscale' : '' }}
                  {{ $activeStatus == 'Done' ? 'ring-2 ring-white/60 scale-[1.02]' : 'hover:bg-emerald-500' }}">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-emerald-200"></span>
                <span class="text-sm font-medium">Status 'Done'</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-bold text-lg">{{ $subTaskStats['done'] }}</span>
                @if($activeStatus == 'Done')
                    <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </span>
                @endif
            </div>
        </a>

    </div>

    <!-- ════════════════════════════════════════════════════════════════
         FILTER BAR — Search, Layanan, Tanggal, Export
         ════════════════════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
        <form method="GET" action="{{ route('dashboard') }}" id="dashboard-filter-form" class="space-y-4">
            {{-- preserve tiket_status from the status cards --}}
            @if($activeStatus)
                <input type="hidden" name="tiket_status" value="{{ $activeStatus }}">
            @endif

            <div class="flex flex-wrap items-end gap-3">

                {{-- Search --}}
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pencarian</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search" id="dash-search"
                               value="{{ $search }}"
                               placeholder="No. Order, Nama PT, Wilayah…"
                               class="w-full pl-8 pr-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition bg-slate-50">
                    </div>
                </div>

                {{-- Layanan Filter --}}
                <div class="min-w-[150px]">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Jenis Layanan</label>
                    <select name="layanan" id="dash-layanan"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition bg-slate-50">
                        <option value="">Semua Layanan</option>
                        @foreach(['Haulage', 'LOLO', 'Penumpukan', 'TKBM'] as $layanan)
                            <option value="{{ $layanan }}" {{ $activeLayanan == $layanan ? 'selected' : '' }}>{{ $layanan }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date From --}}
                <div class="min-w-[140px]">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="date_from" id="dash-date-from"
                           value="{{ $dateFrom }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition bg-slate-50">
                </div>

                {{-- Date To --}}
                <div class="min-w-[140px]">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="date_to" id="dash-date-to"
                           value="{{ $dateTo }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition bg-slate-50">
                </div>

                {{-- Apply & Reset --}}
                <div class="flex gap-2 shrink-0">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('dashboard') }}"
                       class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition flex items-center gap-1.5">
                        <i class="fa-solid fa-xmark"></i> Reset
                    </a>
                </div>

            </div>

            {{-- Active Filter Chips --}}
            @if($activeStatus || $activeLayanan || $dateFrom || $dateTo || $search)
                <div class="flex flex-wrap gap-2 pt-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider self-center">Filter Aktif:</span>
                    @if($activeStatus)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold
                            {{ $activeStatus == 'In' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $activeStatus == 'Out' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $activeStatus == 'Done' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $activeStatus == 'Masuk' ? 'bg-slate-100 text-slate-700' : '' }}">
                            <i class="fa-solid fa-circle text-[8px]"></i> Status: {{ $activeStatus }}
                        </span>
                    @endif
                    @if($activeLayanan)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-violet-100 text-violet-700">
                            <i class="fa-solid fa-layer-group text-[8px]"></i> Layanan: {{ $activeLayanan }}
                        </span>
                    @endif
                    @if($dateFrom || $dateTo)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">
                            <i class="fa-solid fa-calendar text-[8px]"></i>
                            {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '?' }}
                            &ndash;
                            {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '?' }}
                        </span>
                    @endif
                    @if($search)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                            <i class="fa-solid fa-magnifying-glass text-[8px]"></i> "{{ $search }}"
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">
                        {{ $recentOrders->count() }} hasil
                    </span>
                </div>
            @endif

        </form>

        {{-- Export Buttons — use GET with current filter params --}}
        @php
            $exportParams = array_filter([
                'tiket_status' => $activeStatus,
                'layanan'      => $activeLayanan,
                'date_from'    => $dateFrom,
                'date_to'      => $dateTo,
                'search'       => $search,
            ]);
        @endphp
        <div class="flex items-center gap-2 justify-end mt-4 pt-4 border-t border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mr-1">Export data ini:</span>
            <a href="{{ route('dashboard.exportExcel', $exportParams) }}"
               class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl text-xs shadow-sm shadow-emerald-500/20 transition flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i> Export Excel
            </a>
            <a href="{{ route('dashboard.exportPdf', $exportParams) }}"
               class="px-4 py-2 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white font-bold rounded-xl text-xs shadow-sm shadow-rose-500/20 transition flex items-center gap-2">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div id="recent-orders-table" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">
                    Order Terbaru
                    @if($activeStatus)
                        <span class="ml-2 px-2.5 py-0.5 text-xs font-semibold rounded-full
                            {{ $activeStatus == 'In' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $activeStatus == 'Out' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $activeStatus == 'Done' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $activeStatus == 'Masuk' ? 'bg-slate-100 text-slate-700' : '' }}">
                            Filter: {{ $activeStatus }}
                        </span>
                    @endif
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Order dari aplikasi mobile — gunakan filter di atas untuk menyaring data</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('requests.index') }}" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs rounded-xl transition">
                    Lihat Semua <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">No. Order</th>
                        <th class="py-3.5 px-6">Customer / PT</th>
                        <th class="py-3.5 px-6">Source</th>
                        <th class="py-3.5 px-6">Wilayah &amp; Fasilitas</th>
                        <th class="py-3.5 px-6">Tiket Task Pelaksana Lapangan</th>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Status Invoice</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $ord)
                        <tr class="hover:bg-slate-50/80 transition" id="order-row-{{ $ord->id }}">
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

                            {{-- ── Tiket Task Column ─────────────────────────────── --}}
                            <td class="py-4 px-6">
                                <div class="flex flex-col gap-1.5">
                                    @foreach($ord->subTasks as $st)
                                        @php
                                            $showInvoiceLabel = in_array($activeStatus, ['Out', 'Done', 'In']) || (!$activeStatus);
                                        @endphp
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            {{-- Service + Status badge --}}
                                            <div class="inline-flex items-center text-[10px] rounded-lg overflow-hidden border
                                                {{ $st->service_type == 'Haulage' ? 'border-purple-200 bg-purple-50' : '' }}
                                                {{ $st->service_type == 'LOLO' ? 'border-sky-200 bg-sky-50' : '' }}
                                                {{ $st->service_type == 'Penumpukan' ? 'border-amber-200 bg-amber-50' : '' }}
                                                {{ $st->service_type == 'TKBM' ? 'border-teal-200 bg-teal-50' : '' }}
                                                {{ !in_array($st->service_type, ['Haulage', 'LOLO', 'Penumpukan', 'TKBM']) ? 'border-slate-200 bg-slate-50' : '' }}">
                                                <span class="px-2 py-0.5 font-extrabold uppercase
                                                    {{ $st->service_type == 'Haulage' ? 'text-purple-700' : '' }}
                                                    {{ $st->service_type == 'LOLO' ? 'text-sky-700' : '' }}
                                                    {{ $st->service_type == 'Penumpukan' ? 'text-amber-700' : '' }}
                                                    {{ $st->service_type == 'TKBM' ? 'text-teal-700' : '' }}
                                                    {{ !in_array($st->service_type, ['Haulage', 'LOLO', 'Penumpukan', 'TKBM']) ? 'text-slate-700' : '' }}">
                                                    {{ $st->service_type }}
                                                </span>
                                                <span class="px-1.5 py-0.5 font-bold uppercase
                                                    {{ $st->status == 'Masuk' ? 'bg-slate-200 text-slate-700' : '' }}
                                                    {{ $st->status == 'In' ? 'bg-blue-600 text-white' : '' }}
                                                    {{ $st->status == 'Out' ? 'bg-amber-500 text-white' : '' }}
                                                    {{ $st->status == 'Done' ? 'bg-emerald-600 text-white' : '' }}">
                                                    {{ $st->status }}
                                                </span>
                                            </div>

                                            {{-- Invoice label — shown for Out, Done, In, or no filter --}}
                                            @if($showInvoiceLabel)
                                                @if($ord->is_invoiced)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <i class="fa-solid fa-circle-check text-[8px]"></i> Invoice ✓
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                                        <i class="fa-solid fa-circle-xmark text-[8px]"></i> Blm Invoice
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <td class="py-4 px-6 text-xs text-slate-500">
                                {{ $ord->tanggal_order->format('d M Y') }}
                            </td>

                            {{-- ── Invoice Status Column ──────────────────────────── --}}
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="flex flex-col items-start gap-1.5" id="invoice-status-{{ $ord->id }}">
                                    @if($ord->is_invoiced)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i> Sudah Terbit
                                        </span>
                                        @if($ord->invoice_number)
                                            <span class="text-[10px] font-mono text-slate-500">{{ $ord->invoice_number }}</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-circle-xmark text-[10px]"></i> Belum Keluar
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- ── Aksi Column ────────────────────────────────────── --}}
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                    {{-- Invoice confirm/toggle button (AJAX) --}}
                                    <button type="button"
                                            id="invoice-btn-{{ $ord->id }}"
                                            onclick="toggleInvoice({{ $ord->id }}, this)"
                                            title="{{ $ord->is_invoiced ? 'Batalkan Invoice' : 'Konfirmasi Invoice Selesai' }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition
                                                {{ $ord->is_invoiced
                                                    ? 'bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600'
                                                    : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                                        @if($ord->is_invoiced)
                                            <i class="fa-solid fa-rotate-left"></i> Batalkan
                                        @else
                                            <i class="fa-solid fa-check-double"></i> Konfirmasi Invoice
                                        @endif
                                    </button>
                                    {{-- Detail button --}}
                                    <a href="{{ route('requests.show', $ord->id) }}"
                                       class="px-3 py-1.5 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-4xl mb-3 block opacity-30"></i>
                                Belum ada order yang sesuai dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Hidden CSRF token for AJAX --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
// ── Invoice Toggle (AJAX, 1-click) ────────────────────────────────────────
function toggleInvoice(orderId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    fetch(`/requests/${orderId}/toggle-invoice`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-HTTP-Method-Override': 'PATCH',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ _method: 'PATCH' }),
    })
    .then(r => r.json().catch(() => null))
    .then(data => {
        // Reload the row via page fragment replacement
        updateOrderRow(orderId);
    })
    .catch(() => {
        // Fallback: submit a standard form on error
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/requests/${orderId}/toggle-invoice`;
        form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                          <input type="hidden" name="_method" value="PATCH">`;
        document.body.appendChild(form);
        form.submit();
    });
}

// Refresh only the changed row by re-fetching the page and replacing the row innerHTML
function updateOrderRow(orderId) {
    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.text())
    .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const newRow = doc.getElementById(`order-row-${orderId}`);
        const curRow = document.getElementById(`order-row-${orderId}`);
        if (newRow && curRow) curRow.innerHTML = newRow.innerHTML;

        // Also update stats
        const newStatus = doc.getElementById('status-breakdown');
        const curStatus = document.getElementById('status-breakdown');
        if (newStatus && curStatus) curStatus.innerHTML = newStatus.innerHTML;
    })
    .catch(() => location.reload());
}

// ── Background polling (every 5s) — refresh stats + table ─────────────────
function pollDashboardUpdates() {
    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => { if (!r.ok) return; return r.text(); })
    .then(html => {
        if (!html) return;
        const doc = new DOMParser().parseFromString(html, 'text/html');

        const ids = ['kpi-cards', 'status-breakdown', 'recent-orders-table'];
        ids.forEach(id => {
            const cur = document.getElementById(id);
            const nxt = doc.getElementById(id);
            if (cur && nxt) cur.innerHTML = nxt.innerHTML;
        });
    })
    .catch(err => console.error('Dashboard poll error:', err));
}

setInterval(pollDashboardUpdates, 5000);
</script>
@endsection
