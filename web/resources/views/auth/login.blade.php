<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — BKJ Ops Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">
        <!-- Card Header -->
        <div class="p-8 bg-slate-900 text-white text-center relative">
            <div class="w-16 h-16 rounded-2xl bg-blue-600 mx-auto flex items-center justify-center text-2xl font-bold mb-4 shadow-lg shadow-blue-500/40">
                <i class="fa-solid fa-ship"></i>
            </div>
            <h1 class="text-2xl font-bold">BKJ Ops Monitoring</h1>
            <p class="text-xs text-blue-300 mt-1">Masuk ke Portal Monitoring & Administrasi</p>
        </div>

        <!-- Form Section -->
        <div class="p-8">
            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-base shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Admin</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-envelope text-sm"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email', 'admin@bkj.com') }}" required 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                            placeholder="admin@bkj.com">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input type="password" name="password" required value="password"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-600">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded text-blue-600 focus:ring-blue-500">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-lg shadow-blue-600/30 transition flex items-center justify-center gap-2">
                    <span>Masuk ke Dashboard</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Quick Account Selector -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 text-center">
                    Pilih Akun Demo Cepat
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillLogin('admin.allin@bkj.com', 'password')" 
                        class="p-2.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-xl text-left transition group">
                        <div class="font-bold text-xs flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span> Admin ALL IN
                        </div>
                        <div class="text-[10px] text-purple-600/70 truncate mt-0.5">admin.allin@bkj.com</div>
                    </button>

                    <button type="button" onclick="fillLogin('admin.koperasi@bkj.com', 'password')" 
                        class="p-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-left transition group">
                        <div class="font-bold text-xs flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Admin Koperasi
                        </div>
                        <div class="text-[10px] text-amber-700/70 truncate mt-0.5">admin.koperasi@bkj.com</div>
                    </button>

                    <button type="button" onclick="fillLogin('admin.pbmlain@bkj.com', 'password')" 
                        class="p-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-left transition group">
                        <div class="font-bold text-xs flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Admin PBM Lain
                        </div>
                        <div class="text-[10px] text-blue-600/70 truncate mt-0.5">admin.pbmlain@bkj.com</div>
                    </button>

                    <button type="button" onclick="fillLogin('admin@bkj.com', 'password')" 
                        class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl text-left transition group">
                        <div class="font-bold text-xs flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-500"></span> Super Admin
                        </div>
                        <div class="text-[10px] text-slate-500 truncate mt-0.5">admin@bkj.com</div>
                    </button>
                </div>
            </div>
        </div>

        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 text-center text-xs text-slate-500 font-medium">
            PT. Berkah Karya Jasatama &copy; {{ date('Y') }}
        </div>
    </div>

    <script>
        function fillLogin(email, password) {
            const emailInput = document.querySelector('input[name="email"]');
            const passInput = document.querySelector('input[name="password"]');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = password;
                emailInput.focus();
            }
        }
    </script>
</body>
</html>
