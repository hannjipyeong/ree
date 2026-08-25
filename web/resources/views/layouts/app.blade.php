<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BKJ Ops Monitoring') — Bintang Kepri Jaya</title>
    <!-- Tailwind CSS CDN for instant, beautiful styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }
        .bg-navy-900 { background-color: #0d1b2a; }
        .bg-navy-800 { background-color: #1b263b; }
        .bg-navy-700 { background-color: #415a77; }
        .text-navy-900 { color: #0d1b2a; }

        /* Smooth horizontal touch scroll */
        .table-responsive-touch {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ===== NOTIFIKASI PANEL ===== */
        @keyframes bellRing {
            0%,100% { transform: rotate(0deg); }
            10%      { transform: rotate(14deg); }
            20%      { transform: rotate(-12deg); }
            30%      { transform: rotate(10deg); }
            40%      { transform: rotate(-8deg); }
            50%      { transform: rotate(6deg); }
            60%      { transform: rotate(-4deg); }
            70%      { transform: rotate(2deg); }
        }
        .bell-icon:hover .bell-svg { animation: bellRing 0.6s ease; }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        #notif-panel {
            animation: slideDown 0.2s ease;
        }
        .notif-item {
            transition: background 0.15s;
        }
        .notif-item:hover {
            background-color: #f1f5f9;
        }
        .notif-badge-in {
            background: #dbeafe; color: #1d4ed8;
        }
        .notif-badge-out {
            background: #fef3c7; color: #92400e;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800 relative">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebarBackdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm hidden lg:hidden transition-opacity"></div>

    <!-- Sidebar Navigasi (Off-canvas Drawer on Mobile/Tablet, Static on Desktop) -->
    <aside id="mainSidebar" class="fixed lg:static inset-y-0 left-0 w-64 bg-navy-900 text-white flex flex-col justify-between shadow-2xl lg:shadow-xl z-50 shrink-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="overflow-y-auto flex-1">
            <!-- Logo Header -->
            <div class="h-16 sm:h-20 flex items-center justify-between px-6 border-b border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-lg overflow-hidden shrink-0">
                        <img src="{{ asset('images/logo_bkj.jpg') }}" alt="BKJ Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-tight tracking-wide">BKJ</h1>
                        <p class="text-xs text-blue-400 font-medium">Monitoring Dashboard</p>
                    </div>
                </div>
                <!-- Close Button on Mobile Drawer -->
                <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden w-8 h-8 rounded-lg bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Dashboard Utama</span>
                </a>

                <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-400">Modul Operasional</div>

                <a href="{{ route('requests.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ request()->routeIs('requests.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                    <span>Monitoring Request</span>
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ request()->routeIs('customers.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-building-user w-5 text-center"></i>
                    <span>Akun Customer</span>
                </a>

                <a href="{{ route('supir.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm {{ request()->routeIs('supir.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-truck-front w-5 text-center"></i>
                    <span>Akun Pelaksana Lapangan</span>
                </a>

                <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-400">Laporan & Rekap</div>

                <button type="button" onclick="openGlobalExportDoneModal()" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm text-emerald-400 hover:bg-slate-800 hover:text-emerald-300 text-left">
                    <i class="fa-solid fa-file-export w-5 text-center"></i>
                    <span>Ekspor Order Done</span>
                </button>
            </nav>
        </div>

        <!-- Footer / Admin Info & Logout -->
        <div class="p-4 border-t border-slate-700/50 bg-slate-900/50 shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-sm font-semibold text-white shrink-0">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="text-xs min-w-0">
                        <div class="font-semibold text-white truncate">{{ Auth::user()->name ?? 'Admin Ops' }}</div>
                        <div class="text-[11px] text-blue-400 font-medium truncate">{{ Auth::user()->role_title ?? 'Administrator' }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition flex items-center justify-center text-xs" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Top Header -->
        <header class="h-16 sm:h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 shrink-0">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <!-- Hamburger Button for Mobile/Tablet -->
                <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden w-10 h-10 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center justify-center text-base shrink-0 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="min-w-0">
                    <h2 class="text-base sm:text-xl font-bold text-slate-800 truncate">@yield('page_heading', 'Dashboard Overview')</h2>
                </div>

                @if(Auth::user()->admin_source)
                    <span class="hidden sm:inline-flex px-2.5 sm:px-3 py-1 text-[11px] sm:text-xs font-bold rounded-full border shadow-sm shrink-0
                        {{ Auth::user()->admin_source === 'ALL IN' ? 'bg-purple-50 text-purple-700 border-purple-200' : '' }}
                        {{ Auth::user()->admin_source === 'Koperasi' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                        {{ Auth::user()->admin_source === 'PBM Lain' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}">
                        <i class="fa-solid fa-filter text-[10px] me-1"></i> Modul: {{ Auth::user()->admin_source }}
                    </span>
                @else
                    <span class="hidden md:inline-flex px-3 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200 shadow-sm shrink-0">
                        <i class="fa-solid fa-globe text-[10px] me-1"></i> Semua Modul (Super Admin)
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                <span class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    API Connected
                </span>
                <div class="hidden md:block text-xs text-slate-500 font-medium">
                    {{ date('d M Y, H:i') }} WIB
                </div>

                <!-- ===== BELL NOTIFICATION BUTTON ===== -->
                <div class="relative bell-icon" id="notif-wrapper">
                    <button id="notif-btn"
                        onclick="toggleNotifPanel()"
                        class="relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 text-slate-600 hover:text-blue-600 flex items-center justify-center transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <svg class="bell-svg w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <!-- Badge jumlah notifikasi -->
                        <span id="notif-count-badge"
                            class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center hidden">
                            0
                        </span>
                    </button>

                    <!-- ===== NOTIFICATION PANEL DROPDOWN ===== -->
                    <div id="notif-panel"
                        class="hidden absolute right-0 top-12 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200/80 z-50 overflow-hidden"
                        style="max-height: 520px;">

                        <!-- Header Panel -->
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-800 to-slate-700">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="text-white font-bold text-sm">Riwayat Bukti IN/OUT</span>
                            </div>
                            <span id="notif-total-text" class="text-xs text-slate-400 font-medium">Memuat...</span>
                        </div>

                        <!-- List Notifikasi (scrollable) -->
                        <div id="notif-list" class="overflow-y-auto" style="max-height: 420px;">
                            <!-- Konten diisi JS -->
                            <div class="py-10 text-center" id="notif-loading">
                                <div class="inline-flex items-center gap-2 text-slate-400 text-sm">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    Memuat data...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ===== END NOTIFICATION ===== -->

            </div>
        </header>

        <!-- Main Body Scrollable Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                        <span class="text-sm font-semibold">Terdapat kesalahan input:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 text-rose-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Modal Global Export Data Status Done (PDF / Excel) -->
    <div id="globalExportDoneModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl space-y-0">
            <div class="p-5 bg-gradient-to-r from-emerald-800 to-teal-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-emerald-300 font-bold text-lg">
                        <i class="fa-solid fa-file-export"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-base">Ekspor Laporan Order Selesai</h4>
                        <p class="text-xs text-emerald-200 mt-0.5">Khusus data order dengan Status: <strong>DONE</strong></p>
                    </div>
                </div>
                <button type="button" onclick="closeGlobalExportDoneModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="exportDoneForm" method="GET" target="_blank" class="p-6 space-y-5">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Tanggal Awal</label>
                            <input type="date" id="export_start_date" name="start_date" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Tanggal Akhir</label>
                            <input type="date" id="export_end_date" name="end_date" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Filter Modul / Source</label>
                        <select id="export_source" name="source" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua Modul (ALL IN, Koperasi, PBM Lain)</option>
                            <option value="ALL IN">ALL IN</option>
                            <option value="Koperasi">Koperasi</option>
                            <option value="PBM Lain">PBM Lain</option>
                        </select>
                    </div>

                    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-info text-emerald-600 mt-0.5"></i>
                        <div>
                            Kosongkan filter tanggal untuk mengekspor <strong>seluruh data order Done</strong> tanpa batasan waktu.
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-3">
                    <button type="button" onclick="submitExportDone('pdf')" class="py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-md shadow-rose-600/20 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-pdf text-sm"></i>
                        <span>Ekspor ke PDF</span>
                    </button>
                    <button type="button" onclick="submitExportDone('excel')" class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-excel text-sm"></i>
                        <span>Ekspor ke Excel</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>

<script>
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('mainSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (!sidebar) return;

        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            if (backdrop) backdrop.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            if (backdrop) backdrop.classList.add('hidden');
        }
    }

    function openGlobalExportDoneModal() {
        document.getElementById('globalExportDoneModal').classList.remove('hidden');
    }

    function closeGlobalExportDoneModal() {
        document.getElementById('globalExportDoneModal').classList.add('hidden');
    }

    function submitExportDone(type) {
        const form = document.getElementById('exportDoneForm');
        if (type === 'pdf') {
            form.action = "{{ route('requests.exportDonePdf') }}";
        } else {
            form.action = "{{ route('requests.exportDoneExcel') }}";
        }
        form.submit();
    }

    // ===== NOTIFICATION PANEL LOGIC =====
    const NOTIF_URL = '{{ route("notifications") }}';

    // URL builder untuk halaman detail container
    function buildDetailUrl(orderId, containerId) {
        if (orderId && containerId) {
            // Arahkan ke halaman detail container
            return `/requests/${orderId}/containers/${containerId}`;
        } else if (orderId) {
            return `/requests/${orderId}`;
        }
        return '#';
    }

    // Format tanggal lokal
    function fmtTime(isoStr) {
        if (!isoStr) return '-';
        const d = new Date(isoStr);
        return d.toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric'
        }) + ', ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    let notifLoaded = false;

    function toggleNotifPanel() {
        const panel = document.getElementById('notif-panel');
        const isHidden = panel.classList.contains('hidden');

        if (isHidden) {
            panel.classList.remove('hidden');
            if (!notifLoaded) {
                loadNotifications();
            }
        } else {
            panel.classList.add('hidden');
        }
    }

    function loadNotifications() {
        fetch(NOTIF_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            notifLoaded = true;
            const list   = document.getElementById('notif-list');
            const badge  = document.getElementById('notif-count-badge');
            const totTxt = document.getElementById('notif-total-text');

            const data  = res.data || [];
            const total = res.total || 0;

            // Update badge
            if (total > 0) {
                badge.textContent = total > 99 ? '99+' : total;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            totTxt.textContent = total + ' rekaman';

            if (data.length === 0) {
                list.innerHTML = `
                    <div class="py-12 text-center">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p class="text-slate-400 text-sm">Belum ada bukti IN/OUT</p>
                    </div>`;
                return;
            }

            list.innerHTML = data.map(item => {
                const url        = buildDetailUrl(item.order_id, item.container_id);
                const isIn       = item.type === 'IN';
                const badgeCls   = isIn ? 'notif-badge-in' : 'notif-badge-out';
                const badgeIcon  = isIn
                    ? '<i class="fa-solid fa-arrow-down-to-bracket"></i>'
                    : '<i class="fa-solid fa-arrow-up-from-bracket"></i>';
                const containerInfo = item.container_num
                    ? `<span class="font-mono text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">${item.container_num}</span>`
                    : '';
                const noteHtml = item.note
                    ? `<p class="text-[11px] text-slate-400 mt-0.5 truncate">${item.note}</p>`
                    : '';

                return `
                <a href="${url}" class="notif-item flex items-start gap-3 px-5 py-3.5 border-b border-slate-100 last:border-0 cursor-pointer no-underline">
                    <!-- Type Badge -->
                    <span class="mt-0.5 shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold gap-1 ${badgeCls}">
                        ${badgeIcon}
                    </span>
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-slate-700">${item.service_type || '-'}</span>
                            <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded ${badgeCls}">${item.type}</span>
                            ${containerInfo}
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5 truncate">
                            ${item.order_number || ''}
                            ${item.nama_pt ? '· ' + item.nama_pt : ''}
                        </p>
                        ${noteHtml}
                        <p class="text-[10px] text-slate-400 mt-1">
                            <i class="fa-regular fa-clock mr-1"></i>${fmtTime(item.time)}
                        </p>
                    </div>
                    <!-- Arrow -->
                    <span class="mt-2 shrink-0 text-slate-300">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </span>
                </a>`;
            }).join('');
        })
        .catch(() => {
            document.getElementById('notif-list').innerHTML =
                '<p class="py-8 text-center text-red-400 text-sm">Gagal memuat notifikasi.</p>';
        });
    }

    // Klik di luar panel = tutup panel
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('notif-wrapper');
        const panel   = document.getElementById('notif-panel');
        if (wrapper && !wrapper.contains(e.target)) {
            panel.classList.add('hidden');
        }
    });

    // Auto-load badge count di background setiap halaman
    fetch(NOTIF_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(res => {
            const total = res.total || 0;
            const badge = document.getElementById('notif-count-badge');
            if (total > 0) {
                badge.textContent = total > 99 ? '99+' : total;
                badge.classList.remove('hidden');
            }
        }).catch(() => {});

    // Preserve scroll position on main scrollable body
    document.addEventListener('DOMContentLoaded', function() {
        const mainEl = document.querySelector('main');
        if (mainEl) {
            const storageKey = 'bkj_scroll_' + window.location.pathname;
            const savedPos = sessionStorage.getItem(storageKey);
            if (savedPos !== null) {
                mainEl.scrollTop = parseInt(savedPos, 10);
            }
            mainEl.addEventListener('scroll', function() {
                sessionStorage.setItem(storageKey, mainEl.scrollTop);
            }, { passive: true });
        }
    });
</script>
</html>
