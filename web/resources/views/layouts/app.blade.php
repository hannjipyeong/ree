<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BKJ Ops Monitoring') — Berkah Karya Jasatama</title>
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
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">

    <!-- Sidebar Navigasi -->
    <aside class="w-64 bg-navy-900 text-white flex flex-col justify-between shadow-xl z-20 shrink-0">
        <div>
            <!-- Logo Header -->
            <div class="h-20 flex items-center px-6 border-b border-slate-700/50 gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid me-0 fa-ship text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight tracking-wide">BKJ Ops</h1>
                    <p class="text-xs text-blue-400 font-medium">Monitoring Dashboard</p>
                </div>
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
                    <span>Akun Supir</span>
                </a>
            </nav>
        </div>

        <!-- Footer / Admin Info & Logout -->
        <div class="p-4 border-t border-slate-700/50 bg-slate-900/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-sm font-semibold text-white">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="text-xs">
                        <div class="font-semibold text-white">{{ Auth::user()->name ?? 'Admin Ops' }}</div>
                        <div class="text-slate-400">Administrator</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
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
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 shrink-0">
            <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold text-slate-800">@yield('page_heading', 'Dashboard Overview')</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    API Connected
                </span>
                <div class="text-xs text-slate-500 font-medium">
                    {{ date('d M Y, H:i') }} WIB
                </div>
            </div>
        </header>

        <!-- Main Body Scrollable Area -->
        <main class="flex-1 overflow-y-auto p-8">
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

</body>
</html>
