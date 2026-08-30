@extends('layouts.app')

@section('title', 'Monitoring & CRUD Request')
@section('page_heading', 'Monitoring & CRUD Request (Order Mobile App)')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Filters -->
    @php
        $reqFilterCount = 0;
        if (request('search')) $reqFilterCount++;
        if (request('layanan')) $reqFilterCount++;
        if (request('source')) $reqFilterCount++;
        if (request('status')) $reqFilterCount++;
    @endphp

    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
        
        <!-- Search & Filter Bar (Desktop) -->
        <form method="GET" action="{{ route('requests.index') }}" class="hidden lg:flex flex-wrap items-center gap-3">
            <div class="relative min-w-[220px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Order / Nama PT..." 
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
            </div>

            <select name="layanan" onchange="this.form.submit()" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none">
                <option value="">-- Semua Layanan --</option>
                <option value="Railing"     {{ request('layanan') == 'Railing'     ? 'selected' : '' }}>Railing</option>
                <option value="LOLO"        {{ request('layanan') == 'LOLO'        ? 'selected' : '' }}>LOLO</option>
                <option value="Storage"  {{ request('layanan') == 'Storage'  ? 'selected' : '' }}>Storage</option>
                <option value="TKBM"        {{ request('layanan') == 'TKBM'        ? 'selected' : '' }}>TKBM</option>
                <option value="Asuransi"    {{ request('layanan') == 'Asuransi'    ? 'selected' : '' }}>Asuransi</option>
            </select>

            @if(!$adminSource)
            <select name="source" onchange="this.form.submit()" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none">
                <option value="">-- Semua Source --</option>
                <option value="ALL IN"   {{ request('source') == 'ALL IN'   ? 'selected' : '' }}>ALL IN</option>
                <option value="Koperasi" {{ request('source') == 'Koperasi' ? 'selected' : '' }}>Koperasi</option>
            </select>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-900 transition">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'layanan']))
                <a href="{{ route('requests.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">Reset</a>
            @endif
        </form>

        <!-- Search Bar & Drawer Trigger (Mobile/Tablet) -->
        <div class="flex lg:hidden gap-2">
            <form method="GET" action="{{ route('requests.index') }}" class="flex-1 flex gap-2">
                @if(request('layanan'))<input type="hidden" name="layanan" value="{{ request('layanan') }}">@endif
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Order / PT..." 
                        class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shrink-0">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>
            <button type="button" onclick="openRequestFilterDrawer()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 shrink-0 border border-slate-200">
                <i class="fa-solid fa-sliders text-blue-600"></i>
                <span>Filter</span>
                @if($reqFilterCount > 0)
                    <span class="w-4 h-4 rounded-full bg-blue-600 text-white text-[9px] font-extrabold flex items-center justify-center">{{ $reqFilterCount }}</span>
                @endif
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full lg:w-auto justify-end">
            <!-- Export Done Data Button -->
            <button type="button" onclick="openGlobalExportDoneModal()" class="flex-1 sm:flex-initial px-3 sm:px-4 py-2 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-export text-xs"></i>
                <span>Ekspor Done</span>
            </button>

            <!-- New Request Button -->
            <a href="{{ route('requests.create') }}" class="flex-1 sm:flex-initial px-3 sm:px-4 py-2 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-blue-600/30 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Order Manual</span>
            </a>
        </div>
    </div>

    <!-- Right Sidebar Filter Drawer for Requests (Mobile/Tablet) -->
    <div id="requestFilterBackdrop" onclick="closeRequestFilterDrawer()" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity"></div>

    <div id="requestFilterDrawer" class="fixed inset-y-0 right-0 w-80 sm:w-96 bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-900 text-white shrink-0">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-sliders text-blue-400"></i>
                <h4 class="font-bold text-sm">Filter Request Order</h4>
            </div>
            <button type="button" onclick="closeRequestFilterDrawer()" class="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="GET" action="{{ route('requests.index') }}" class="flex-1 overflow-y-auto p-5 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Kunci</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Order / PT..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Layanan</label>
                <select name="layanan" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
                    <option value="">-- Semua Layanan --</option>
                    <option value="Railing"     {{ request('layanan') == 'Railing'     ? 'selected' : '' }}>Railing</option>
                    <option value="LOLO"        {{ request('layanan') == 'LOLO'        ? 'selected' : '' }}>LOLO</option>
                    <option value="Storage"  {{ request('layanan') == 'Storage'  ? 'selected' : '' }}>Storage</option>
                    <option value="TKBM"        {{ request('layanan') == 'TKBM'        ? 'selected' : '' }}>TKBM</option>
                    <option value="Asuransi"    {{ request('layanan') == 'Asuransi'    ? 'selected' : '' }}>Asuransi</option>
                </select>
            </div>

            @if(!$adminSource)
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Source Order</label>
                <select name="source" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
                    <option value="">-- Semua Source --</option>
                    <option value="ALL IN"   {{ request('source') == 'ALL IN'   ? 'selected' : '' }}>ALL IN</option>
                    <option value="Koperasi" {{ request('source') == 'Koperasi' ? 'selected' : '' }}>Koperasi</option>
                </select>
            </div>
            @endif

            <div class="pt-4 border-t border-slate-100 flex gap-2">
                <a href="{{ route('requests.index') }}" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs text-center transition">
                    Reset
                </a>
                <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Request Table -->
    <div id="requests-table" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">No. Order</th>
                        <th class="py-3.5 px-6">Source</th>
                        <th class="py-3.5 px-6">Nama PT & PBM</th>
                        <th class="py-3.5 px-6">Lokasi & Kegiatan</th>
                        <th class="py-3.5 px-6">Detail Muatan</th>
                        <th class="py-3.5 px-6">Status Tiket Pelaksana Lapangan</th>

                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $ord)
                        @php
                            $isDone = $ord->status === 'Done' || $ord->subTasks->every(fn($st) => $st->status === 'Done');
                            $isKoperasi = strcasecmp($ord->source, 'Koperasi') === 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-blue-600">
                                {{ $ord->order_number }}
                                <div class="text-[11px] text-slate-400 font-normal mt-0.5">
                                    {{ $ord->tanggal_order ? $ord->tanggal_order->format('d M Y') : '-' }}
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
                                @if(strtolower($ord->payload_type) === 'cargo')
                                    <div class="inline-flex flex-col items-start gap-0.5">
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold flex items-center gap-1.5">
                                            <i class="fa-solid fa-boxes-packing text-amber-600"></i> Cargo
                                        </span>
                                        <span class="text-[11px] text-slate-500 font-medium">
                                            {{ $ord->jumlah_tonase ? str_replace('.', ',', (float)$ord->jumlah_tonase) . ' Ton' : ($ord->jenis_barang ?: 'General Cargo') }}
                                        </span>
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-medium inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-box text-blue-600"></i> Container ({{ $ord->containers->count() }})
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col gap-1.5">
                                    @foreach($ord->subTasks as $st)
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase border
                                                {{ $st->service_type == 'Railing' ? 'bg-purple-100 text-purple-800 border-purple-200' : '' }}
                                                {{ $st->service_type == 'LOLO' ? 'bg-sky-100 text-sky-800 border-sky-200' : '' }}
                                                {{ $st->service_type == 'Storage' ? 'bg-amber-100 text-amber-800 border-amber-200' : '' }}
                                                {{ $st->service_type == 'TKBM' ? 'bg-teal-100 text-teal-800 border-teal-200' : '' }}
                                                {{ !in_array($st->service_type, ['Railing', 'LOLO', 'Storage', 'TKBM']) ? 'bg-slate-100 text-slate-700 border-slate-200' : '' }}">
                                                {{ $st->service_type }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase
                                                {{ $st->status == 'Masuk' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}
                                                {{ $st->status == 'In' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                                                {{ $st->status == 'Out' ? 'bg-amber-100 text-amber-700 border border-amber-200' : '' }}
                                                {{ $st->status == 'Done' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}">
                                                {{ $st->status }}
                                            </span>
                                            @if($st->supir)
                                                <span class="text-[10px] text-slate-400 font-medium">({{ $st->supir->name }})</span>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($ord->has_asuransi)
                                        <div class="text-[10px] font-bold text-emerald-700 flex items-center gap-1">
                                            <i class="fa-solid fa-shield-halved text-[9px]"></i> Asuransi Aktif
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="py-4 px-6 text-right space-x-1 whitespace-nowrap">
                                @if($isKoperasi)
                                    <span class="px-2.5 py-1.5 bg-slate-100 text-slate-400 rounded-lg text-xs font-semibold inline-flex items-center gap-1 cursor-not-allowed opacity-60" title="Fungsi Ekspor Surat dinonaktifkan khusus pada request Koperasi">
                                        <i class="fa-solid fa-ban"></i>
                                        <span>Surat PDF (Off)</span>
                                    </span>
                                @else
                                    <a href="{{ route('requests.exportPdf', $ord->id) }}" target="_blank" class="px-2.5 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-semibold transition inline-flex items-center gap-1 shadow-sm" title="Ekspor Surat PDF Permohonan">
                                        <i class="fa-solid fa-file-pdf"></i>
                                        <span>Surat PDF</span>
                                    </a>
                                @endif

                                <a href="{{ route('requests.show', $ord->id) }}" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </a>

                                <form action="{{ route('requests.destroy', $ord->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus order ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 bg-slate-100 text-slate-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-semibold transition" title="Hapus Order">
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

<script>
    function openRequestFilterDrawer() {
        const drawer = document.getElementById('requestFilterDrawer');
        const backdrop = document.getElementById('requestFilterBackdrop');
        if (drawer) drawer.classList.remove('translate-x-full');
        if (backdrop) backdrop.classList.remove('hidden');
    }

    function closeRequestFilterDrawer() {
        const drawer = document.getElementById('requestFilterDrawer');
        const backdrop = document.getElementById('requestFilterBackdrop');
        if (drawer) drawer.classList.add('translate-x-full');
        if (backdrop) backdrop.classList.add('hidden');
    }

    function isUserInteracting() {
        const activeEl = document.activeElement;
        if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT')) {
            return true;
        }
        const openModals = document.querySelectorAll('.fixed:not(.hidden), [id*="modal"]:not(.hidden), [id*="Modal"]:not(.hidden)');
        for (let m of openModals) {
            if (!m.classList.contains('hidden') && m.offsetParent !== null) return true;
        }
        return false;
    }

    function pollRequestsUpdates() {
        if (isUserInteracting()) return;

        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) return;
            return response.text();
        })
        .then(html => {
            if (!html || isUserInteracting()) return;
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const table = document.getElementById('requests-table');
            const newTable = doc.getElementById('requests-table');
            if (table && newTable) table.innerHTML = newTable.innerHTML;
        })
        .catch(err => console.error("Error polling requests updates:", err));
    }

    // Poll every 5 seconds safely
    setInterval(pollRequestsUpdates, 5000);
</script>
@endsection

