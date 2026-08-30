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
    <div id="status-breakdown" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

        {{-- Masuk --}}
        <a href="{{ $activeStatus == 'Masuk' ? route('dashboard') : route('dashboard', array_merge(request()->except('tiket_status'), ['tiket_status' => 'Masuk'])) }}"
           onclick="event.preventDefault(); applyFilterAjax(this.href);"
           class="group p-3 sm:p-4 rounded-xl flex items-center justify-between transition-all duration-200 cursor-pointer
                  bg-slate-800 text-white shadow
                  {{ $activeStatus && $activeStatus != 'Masuk' ? 'opacity-40 grayscale' : '' }}
                  {{ $activeStatus == 'Masuk' ? 'ring-2 ring-white/60 scale-[1.02]' : 'hover:bg-slate-700' }}">
            <div class="flex items-center gap-2 sm:gap-3">
                <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-slate-400 {{ $activeStatus == 'Masuk' ? 'animate-pulse' : '' }}"></span>
                <span class="text-xs sm:text-sm font-medium">Status 'Masuk'</span>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="font-bold text-base sm:text-lg">{{ $subTaskStats['masuk'] }}</span>
                @if($activeStatus == 'Masuk')
                    <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-check text-[9px]"></i>
                    </span>
                @endif
            </div>
        </a>

        {{-- IN --}}
        <a href="{{ $activeStatus == 'In' ? route('dashboard') : route('dashboard', array_merge(request()->except('tiket_status'), ['tiket_status' => 'In'])) }}"
           onclick="event.preventDefault(); applyFilterAjax(this.href);"
           class="group p-3 sm:p-4 rounded-xl flex items-center justify-between transition-all duration-200 cursor-pointer
                  bg-blue-600 text-white shadow-lg shadow-blue-600/20
                  {{ $activeStatus && $activeStatus != 'In' ? 'opacity-40 grayscale' : '' }}
                  {{ $activeStatus == 'In' ? 'ring-2 ring-white/60 scale-[1.02]' : 'hover:bg-blue-500' }}">
            <div class="flex items-center gap-2 sm:gap-3">
                <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-blue-200 {{ $activeStatus == 'In' ? 'animate-ping' : '' }}"></span>
                <span class="text-xs sm:text-sm font-medium">Status 'IN'</span>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="font-bold text-base sm:text-lg">{{ $subTaskStats['in'] }}</span>
                @if($activeStatus == 'In')
                    <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-check text-[9px]"></i>
                    </span>
                @endif
            </div>
        </a>

        {{-- OUT --}}
        <a href="{{ $activeStatus == 'Out' ? route('dashboard') : route('dashboard', array_merge(request()->except('tiket_status'), ['tiket_status' => 'Out'])) }}"
           onclick="event.preventDefault(); applyFilterAjax(this.href);"
           class="group p-3 sm:p-4 rounded-xl flex items-center justify-between transition-all duration-200 cursor-pointer
                  bg-amber-500 text-white shadow-lg shadow-amber-500/20
                  {{ $activeStatus && $activeStatus != 'Out' ? 'opacity-40 grayscale' : '' }}
                  {{ $activeStatus == 'Out' ? 'ring-2 ring-white/60 scale-[1.02]' : 'hover:bg-amber-400' }}">
            <div class="flex items-center gap-2 sm:gap-3">
                <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-amber-200"></span>
                <span class="text-xs sm:text-sm font-medium">Status 'OUT'</span>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="font-bold text-base sm:text-lg">{{ $subTaskStats['out'] }}</span>
                @if($activeStatus == 'Out')
                    <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-check text-[9px]"></i>
                    </span>
                @endif
            </div>
        </a>

        {{-- Done --}}
        <a href="{{ $activeStatus == 'Done' ? route('dashboard') : route('dashboard', array_merge(request()->except('tiket_status'), ['tiket_status' => 'Done'])) }}"
           onclick="event.preventDefault(); applyFilterAjax(this.href);"
           class="group p-3 sm:p-4 rounded-xl flex items-center justify-between transition-all duration-200 cursor-pointer
                  bg-emerald-600 text-white shadow-lg shadow-emerald-600/20
                  {{ $activeStatus && $activeStatus != 'Done' ? 'opacity-40 grayscale' : '' }}
                  {{ $activeStatus == 'Done' ? 'ring-2 ring-white/60 scale-[1.02]' : 'hover:bg-emerald-500' }}">
            <div class="flex items-center gap-2 sm:gap-3">
                <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-emerald-200"></span>
                <span class="text-xs sm:text-sm font-medium">Status 'Done'</span>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="font-bold text-base sm:text-lg">{{ $subTaskStats['done'] }}</span>
                @if($activeStatus == 'Done')
                    <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-check text-[9px]"></i>
                    </span>
                @endif
            </div>
        </a>

    </div>

    <!-- ════════════════════════════════════════════════════════════════
         FILTER BAR — Search, Layanan, Tanggal, Export (Responsive & Shopee Drawer)
         ════════════════════════════════════════════════════════════════ -->
    @php
        $activeFilterCount = 0;
        if ($activeStatus) $activeFilterCount++;
        if ($activeLayanan) $activeFilterCount++;
        if (!empty($activePayload)) $activeFilterCount++;
        if ($dateFrom) $activeFilterCount++;
        if ($dateTo) $activeFilterCount++;
        if ($search) $activeFilterCount++;
    @endphp

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-5">
        
        <!-- Mobile Search & Filter Trigger (lg:hidden) -->
        <div class="block lg:hidden space-y-3">
            <form method="GET" action="{{ route('dashboard') }}" class="flex gap-2">
                @if($activeStatus)<input type="hidden" name="tiket_status" value="{{ $activeStatus }}">@endif
                @if($activeLayanan)<input type="hidden" name="layanan" value="{{ $activeLayanan }}">@endif
                @if(!empty($activePayload))<input type="hidden" name="payload_type" value="{{ $activePayload }}">@endif
                @if($dateFrom)<input type="hidden" name="date_from" value="{{ $dateFrom }}">@endif
                @if($dateTo)<input type="hidden" name="date_to" value="{{ $dateTo }}">@endif

                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari Order / PT..." class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
                </div>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shrink-0">
                    <i class="fa-solid fa-search"></i>
                </button>
                <button type="button" onclick="openDashboardFilterDrawer()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 shrink-0 border border-slate-200">
                    <i class="fa-solid fa-sliders text-blue-600"></i>
                    <span>Filter</span>
                    @if($activeFilterCount > 0)
                        <span class="w-4 h-4 rounded-full bg-blue-600 text-white text-[9px] font-extrabold flex items-center justify-center">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </form>
        </div>

        <!-- Desktop Full Filter Form (hidden lg:block) -->
        <form method="GET" action="{{ route('dashboard') }}" id="dashboard-filter-form" class="hidden lg:block space-y-4">
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
                        @foreach(['Railing', 'LOLO', 'Storage', 'TKBM'] as $layanan)
                            <option value="{{ $layanan }}" {{ $activeLayanan == $layanan ? 'selected' : '' }}>{{ $layanan }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipe Muatan Filter (Container vs Cargo) --}}
                <div class="min-w-[150px]">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tipe Muatan</label>
                    <select name="payload_type" id="dash-payload-type"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition bg-slate-50">
                        <option value="">Semua Tipe</option>
                        <option value="Container" {{ ($activePayload ?? '') == 'Container' ? 'selected' : '' }}>Kontainer</option>
                        <option value="Cargo" {{ ($activePayload ?? '') == 'Cargo' ? 'selected' : '' }}>Cargo (Muatan Bebas)</option>
                        <option value="Both" {{ ($activePayload ?? '') == 'Both' || ($activePayload ?? '') == 'Container,Cargo' ? 'selected' : '' }}>Kontainer & Cargo</option>
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
        </form>

        {{-- Active Filter Chips --}}
        @if($activeStatus || $activeLayanan || ($activePayload ?? null) || $dateFrom || $dateTo || $search)
            <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-100 mt-3">
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
                @if(!empty($activePayload))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700">
                        <i class="fa-solid fa-boxes-stacked text-[8px]"></i> Tipe: {{ $activePayload == 'Container' ? 'Kontainer' : ($activePayload == 'Cargo' ? 'Cargo' : 'Kontainer & Cargo') }}
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

        {{-- Export Buttons — use GET with current filter params --}}
        @php
            $exportParams = array_filter([
                'tiket_status' => $activeStatus,
                'layanan'      => $activeLayanan,
                'payload_type' => $activePayload ?? null,
                'date_from'    => $dateFrom,
                'date_to'      => $dateTo,
                'search'       => $search,
            ]);
        @endphp
        <div class="flex flex-wrap items-center gap-2 justify-between sm:justify-end mt-4 pt-4 border-t border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Export data ini:</span>
            <div class="flex gap-2">
                <a href="{{ route('dashboard.exportExcel', $exportParams) }}"
                   class="px-3 sm:px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl text-xs shadow-sm shadow-emerald-500/20 transition flex items-center gap-1.5 sm:gap-2">
                    <i class="fa-solid fa-file-csv"></i> <span>Excel</span>
                </a>
                <a href="{{ route('dashboard.exportPdf', $exportParams) }}"
                   class="px-3 sm:px-4 py-2 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white font-bold rounded-xl text-xs shadow-sm shadow-rose-500/20 transition flex items-center gap-1.5 sm:gap-2">
                    <i class="fa-solid fa-file-pdf"></i> <span>PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         SHOPEE-STYLE RIGHT SIDEBAR FILTER DRAWER (Mobile / Tablet)
         ════════════════════════════════════════════════════════════════ -->
    <div id="dashFilterBackdrop" onclick="closeDashboardFilterDrawer()" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity"></div>

    <div id="dashFilterDrawer" class="fixed inset-y-0 right-0 w-80 sm:w-96 bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-900 text-white shrink-0">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-sliders text-blue-400"></i>
                <h4 class="font-bold text-sm">Filter &amp; Opsi Lanjutan</h4>
            </div>
            <button type="button" onclick="closeDashboardFilterDrawer()" class="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body Form -->
        <form method="GET" action="{{ route('dashboard') }}" class="flex-1 overflow-y-auto p-5 space-y-4">
            <!-- Search -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Kunci Pencarian</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="No. Order, PT, Wilayah..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
            </div>

            <!-- Status Tiket Selection (Pills) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Tiket Pelaksana</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['' => 'Semua', 'Masuk' => 'Masuk', 'In' => 'IN', 'Out' => 'OUT', 'Done' => 'Done'] as $val => $lbl)
                        <label class="flex items-center gap-2 p-2 border rounded-xl cursor-pointer text-xs font-medium {{ $activeStatus == $val ? 'bg-blue-50 border-blue-300 text-blue-700 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                            <input type="radio" name="tiket_status" value="{{ $val }}" {{ $activeStatus == $val ? 'checked' : '' }} class="text-blue-600">
                            <span>{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Layanan Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Layanan</label>
                <select name="layanan" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
                    <option value="">Semua Layanan</option>
                    @foreach(['Railing', 'LOLO', 'Storage', 'TKBM'] as $layanan)
                        <option value="{{ $layanan }}" {{ $activeLayanan == $layanan ? 'selected' : '' }}>{{ $layanan }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tipe Muatan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Muatan</label>
                <select name="payload_type" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
                    <option value="">Semua Tipe</option>
                    <option value="Container" {{ ($activePayload ?? '') == 'Container' ? 'selected' : '' }}>Kontainer</option>
                    <option value="Cargo" {{ ($activePayload ?? '') == 'Cargo' ? 'selected' : '' }}>Cargo</option>
                    <option value="Both" {{ ($activePayload ?? '') == 'Both' || ($activePayload ?? '') == 'Container,Cargo' ? 'selected' : '' }}>Kontainer &amp; Cargo</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">Rentang Tanggal Order</label>
                <div>
                    <span class="text-[10px] text-slate-400 block mb-1">Dari Tanggal:</span>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50">
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block mb-1">Sampai Tanggal:</span>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50">
                </div>
            </div>

            <!-- Footer Action Buttons inside Drawer -->
            <div class="pt-4 border-t border-slate-100 flex gap-2">
                <a href="{{ route('dashboard') }}" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs text-center transition">
                    Reset
                </a>
                <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow">
                    Terapkan Filter
                </button>
            </div>
        </form>
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
                        <th class="py-3.5 px-6">Tipe / Muatan</th>
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
                            
                            {{-- ── Tipe Cargo / Container Column ───────────────── --}}
                            <td class="py-4 px-6">
                                @php
                                    $hasContainers = $ord->containers->isNotEmpty();
                                    $isCargo = str_contains(strtolower($ord->payload_type), 'cargo') || $ord->containers->isEmpty();
                                @endphp
                                <div class="flex flex-col gap-1.5">
                                    @if($hasContainers)
                                        <div>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fa-solid fa-box text-[10px]"></i> Kontainer ({{ $ord->containers->count() }})
                                            </span>
                                            <div class="text-[11px] text-slate-500 font-medium truncate max-w-[140px] mt-0.5" title="{{ $ord->containers->pluck('container_number')->filter()->implode(', ') }}">
                                                {{ $ord->containers->first()->container_size }} {{ $ord->containers->first()->container_type }}
                                                @if($ord->containers->count() > 1) <span class="text-slate-400">+{{ $ord->containers->count() - 1 }}</span> @endif
                                            </div>
                                        </div>
                                    @endif
                                    @if($isCargo)
                                        <div>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i class="fa-solid fa-boxes-packing text-[10px]"></i> Cargo
                                            </span>
                                            <div class="text-[11px] text-slate-500 font-medium truncate max-w-[140px] mt-0.5" title="{{ $ord->jenis_barang }}">
                                                {{ $ord->jenis_barang ?: 'General Cargo' }}
                                                @if($ord->jumlah_tonase) <span class="text-amber-600 font-bold">({{ str_replace('.', ',', (float)$ord->jumlah_tonase) }} T)</span> @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="py-4 px-6">
                                <div class="text-slate-800 font-medium">{{ $ord->wilayah }}</div>
                                <div class="text-xs text-slate-400">{{ $ord->lokasi_fasilitas }} ({{ $ord->jenis_kegiatan }})</div>
                            </td>

                            {{-- ── Tiket Task Column ─────────────────────────────── --}}
                            @php
                                // Determine which sub-task statuses to SHOW based on active filter
                                // IN   → only show tikets with status In
                            // OUT  → show tikets with status In + Out
                            // Done → show tikets with status In + Out + Done (not Masuk)
                            // no filter / Masuk → show all
                            $allowedStatuses = match($activeStatus) {
                                'In'   => ['In'],
                                'Out'  => ['Masuk', 'In', 'Out'],
                                'Done' => ['Masuk', 'In', 'Out', 'Done'],
                                default => null, // null = show all
                            };
                            $visibleTasks = $ord->subTasks;
                            if ($allowedStatuses) {
                                $visibleTasks = $visibleTasks->filter(fn($st) => in_array($st->status, $allowedStatuses));
                            }
                            if ($activeLayanan) {
                                $visibleTasks = $visibleTasks->filter(fn($st) => $st->service_type == $activeLayanan);
                            }
                            @endphp
                            <td class="py-4 px-6">
                                <div class="flex flex-col gap-1">
                                    {{-- Tiket badges (filtered by active status) --}}
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($visibleTasks as $st)
                                            @php
                                                $history = [];
                                                if ($st->status == 'Masuk') {
                                                    $history = ['Masuk'];
                                                } elseif ($st->status == 'In') {
                                                    $history = ['In'];
                                                } elseif ($st->status == 'Out') {
                                                    $history = ['In', 'Out'];
                                                } elseif ($st->status == 'Done') {
                                                    $history = ['In', 'Out', 'Done'];
                                                }
                                                // If filtering by a specific status, limit by allowedStatuses
                                                $history = array_intersect($history, $allowedStatuses ?? ['Masuk', 'In', 'Out', 'Done']);
                                            @endphp

                                            @foreach($history as $hStatus)
                                                <div class="inline-flex items-center text-[10px] rounded-lg overflow-hidden border
                                                    {{ $st->service_type == 'Railing'    ? 'border-purple-200 bg-purple-50' : '' }}
                                                    {{ $st->service_type == 'LOLO'       ? 'border-sky-200 bg-sky-50'       : '' }}
                                                    {{ $st->service_type == 'Storage' ? 'border-amber-200 bg-amber-50'   : '' }}
                                                    {{ $st->service_type == 'TKBM'       ? 'border-teal-200 bg-teal-50'     : '' }}
                                                    {{ !in_array($st->service_type, ['Railing','LOLO','Storage','TKBM']) ? 'border-slate-200 bg-slate-50' : '' }}">
                                                    <span class="px-2 py-0.5 font-extrabold uppercase
                                                        {{ $st->service_type == 'Railing'    ? 'text-purple-700' : '' }}
                                                        {{ $st->service_type == 'LOLO'       ? 'text-sky-700'    : '' }}
                                                        {{ $st->service_type == 'Storage' ? 'text-amber-700'  : '' }}
                                                        {{ $st->service_type == 'TKBM'       ? 'text-teal-700'   : '' }}
                                                        {{ !in_array($st->service_type, ['Railing','LOLO','Storage','TKBM']) ? 'text-slate-700' : '' }}">
                                                        {{ $st->service_type }}
                                                    </span>
                                                    <span class="px-1.5 py-0.5 font-bold uppercase
                                                        {{ $hStatus == 'Masuk' ? 'bg-slate-200 text-slate-700' : '' }}
                                                        {{ $hStatus == 'In'    ? 'bg-blue-600 text-white'      : '' }}
                                                        {{ $hStatus == 'Out'   ? 'bg-amber-500 text-white'     : '' }}
                                                        {{ $hStatus == 'Done'  ? 'bg-emerald-600 text-white'   : '' }}">
                                                        {{ $hStatus }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        @endforeach
                                        @if($visibleTasks->isEmpty())
                                            <span class="text-[10px] text-slate-400 italic">Tidak ada tiket {{ $activeStatus }}</span>
                                        @endif
                                    </div>


                                </div>
                            </td>

                            <td class="py-4 px-6 text-xs text-slate-500">
                                {{ $ord->tanggal_order->format('d M Y') }}
                            </td>

                            {{-- ── Invoice Status Column ──────────────────────────── --}}
                            <td class="py-4 px-6 relative invoice-dropdown-container" id="invoice-status-{{ $ord->id }}">
                                @php
                                    $totalProgresses = 0;
                                    $invoicedProgresses = 0;
                                    foreach($ord->containers as $c) {
                                        $totalProgresses += $c->progresses->count();
                                        $invoicedProgresses += $c->progresses->where('is_invoiced', true)->count();
                                    }
                                @endphp
                                
                                <button type="button" 
                                        onclick="document.getElementById('invoice-dropdown-{{ $ord->id }}').classList.toggle('hidden')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-colors shadow-sm focus:outline-none
                                        {{ $invoicedProgresses == $totalProgresses && $totalProgresses > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : ($invoicedProgresses > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200') }}">
                                    @if($invoicedProgresses == $totalProgresses && $totalProgresses > 0)
                                        <i class="fa-solid fa-check-double text-[10px]"></i> Semua Terbit
                                    @elseif($invoicedProgresses > 0)
                                        <i class="fa-solid fa-file-invoice text-[10px]"></i> {{ $invoicedProgresses }} / {{ $totalProgresses }} Terbit
                                    @else
                                        <i class="fa-solid fa-file-invoice text-[10px]"></i> Belum Terbit
                                    @endif
                                    <i class="fa-solid fa-chevron-down text-[9px] ml-1 opacity-50"></i>
                                </button>
                                
                                <!-- Dropdown menu -->
                                <div id="invoice-dropdown-{{ $ord->id }}" class="invoice-dropdown-menu hidden absolute left-6 top-14 mt-1 w-64 bg-white rounded-xl shadow-xl border border-slate-200 z-[99] overflow-hidden">
                                    <div class="max-h-60 overflow-y-auto p-2 space-y-2">
                                        @foreach($ord->containers as $c)
                                            @if($c->progresses->count() > 0)
                                                <div class="border border-slate-100 rounded-lg p-2 bg-slate-50">
                                                    <div class="text-[10px] font-bold text-slate-800 mb-1 border-b border-slate-200 pb-1 flex justify-between">
                                                        <span>{{ $c->container_number ?? 'No-ID' }} <span class="text-slate-400 font-normal">({{ $c->container_size }})</span></span>
                                                    </div>
                                                    <div class="space-y-1.5 mt-1.5">
                                                        @foreach($c->progresses as $prog)
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-[10px] font-semibold text-slate-600 uppercase">{{ $prog->subTask->service_type ?? 'Layanan' }}</span>
                                                                
                                                                <button type="button"
                                                                        id="invoice-btn-prog-{{ $prog->id }}"
                                                                        onclick="toggleInvoiceProgress({{ $prog->id }}, {{ $ord->id }}, this)"
                                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold transition-colors focus:outline-none
                                                                        {{ $prog->is_invoiced ? 'bg-emerald-100 text-emerald-700 hover:bg-rose-100 hover:text-rose-700 border border-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-emerald-100 hover:text-emerald-700 border border-slate-300' }}">
                                                                    @if($prog->is_invoiced)
                                                                        <i class="fa-solid fa-check"></i> Terbit
                                                                    @else
                                                                        <i class="fa-solid fa-xmark"></i> Belum
                                                                    @endif
                                                                </button>
                                                            </div>
                                                            @if($prog->is_invoiced && $prog->invoice_number)
                                                                <div class="text-[8px] font-mono text-slate-400 mt-0.5 text-right border-b border-slate-100 pb-1" id="invoice-num-prog-{{ $prog->id }}">
                                                                    {{ $prog->invoice_number }}
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        @if($totalProgresses == 0)
                                            <div class="text-[10px] text-slate-400 italic text-center p-2">Belum ada progres layanan diorder ini.</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- ── Aksi Column ──── --}}
                            <td class="py-4 px-6 text-right">
                                <div class="flex flex-col items-end gap-1.5">
                                    {{-- Detail button --}}
                                    <a href="{{ route('requests.show', $ord->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-600 rounded-lg text-xs font-semibold transition">
                                        <i class="fa-solid fa-eye text-[11px]"></i> Detail
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
function toggleInvoiceProgress(progressId, orderId, btn) {
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    }

    fetch(`/requests/progress/${progressId}/toggle-invoice`, {
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
        // Reload the row via page fragment replacement to update UI automatically
        updateOrderRow(orderId);
    })
    .catch(() => {
        alert('Terjadi kesalahan jaringan saat mengubah status invoice.');
        if (btn) btn.disabled = false;
    });
}

// Helper untuk menutup dropdown saat klik di luar
document.addEventListener('click', function(e) {
    if (!e.target.closest('.invoice-dropdown-container')) {
        document.querySelectorAll('.invoice-dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

function openDashboardFilterDrawer() {
    const drawer = document.getElementById('dashFilterDrawer');
    const backdrop = document.getElementById('dashFilterBackdrop');
    if (drawer) drawer.classList.remove('translate-x-full');
    if (backdrop) backdrop.classList.remove('hidden');
}

function closeDashboardFilterDrawer() {
    const drawer = document.getElementById('dashFilterDrawer');
    const backdrop = document.getElementById('dashFilterBackdrop');
    if (drawer) drawer.classList.add('translate-x-full');
    if (backdrop) backdrop.classList.add('hidden');
}

// ── Seamless AJAX Filter (Zero Page Jump / Preserves Exact Scroll Position) ────
function applyFilterAjax(url) {
    const mainEl = document.querySelector('main');
    const prevScrollTop = mainEl ? mainEl.scrollTop : window.scrollY;

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.text())
    .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        // Update target sections in place
        const targetIds = ['status-breakdown', 'recent-orders-table', 'dashboard-filter-form', 'kpi-cards'];
        targetIds.forEach(id => {
            const cur = document.getElementById(id);
            const nxt = doc.getElementById(id);
            if (cur && nxt) cur.innerHTML = nxt.innerHTML;
        });

        // Update URL without page reload
        window.history.pushState(null, '', url);

        // Retain exact scroll position
        if (mainEl) {
            mainEl.scrollTop = prevScrollTop;
        } else {
            window.scrollTo(0, prevScrollTop);
        }
    })
    .catch(err => {
        console.error('Filter AJAX error:', err);
        window.location.href = url;
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

// ── Background polling (every 6s) — refresh stats + table safely ─────────────────
function isUserInteracting() {
    const activeEl = document.activeElement;
    if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT')) {
        return true;
    }
    // Check open dropdowns
    const openDropdowns = document.querySelectorAll('.invoice-dropdown-menu:not(.hidden)');
    if (openDropdowns.length > 0) return true;
    
    // Check open modals
    const openModals = document.querySelectorAll('.fixed:not(.hidden), [id*="modal"]:not(.hidden), [id*="Modal"]:not(.hidden)');
    for (let m of openModals) {
        if (!m.classList.contains('hidden') && m.offsetParent !== null) return true;
    }
    return false;
}

function pollDashboardUpdates() {
    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => { if (!r.ok) return; return r.text(); })
    .then(html => {
        if (!html) return;
        const doc = new DOMParser().parseFromString(html, 'text/html');

        // Always update KPI cards and status breakdown
        const statIds = ['kpi-cards', 'status-breakdown'];
        statIds.forEach(id => {
            const cur = document.getElementById(id);
            const nxt = doc.getElementById(id);
            if (cur && nxt) cur.innerHTML = nxt.innerHTML;
        });

        // Only update recent-orders-table if user is NOT currently interacting/typing/opening dropdowns
        if (!isUserInteracting()) {
            const cur = document.getElementById('recent-orders-table');
            const nxt = doc.getElementById('recent-orders-table');
            if (cur && nxt) cur.innerHTML = nxt.innerHTML;
        }
    })
    .catch(err => console.error('Dashboard poll error:', err));
}

setInterval(pollDashboardUpdates, 6000);
</script>
@endsection
