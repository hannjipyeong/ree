@extends('layouts.app')

@section('title', 'Monitoring & CRUD Akun Supir')
@section('page_heading', 'Monitoring & CRUD Akun Supir (Driver Operasional)')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Search -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        
        <form method="GET" action="{{ route('supir.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative min-w-[240px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / Email / No. HP..." 
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
            </div>

            <select name="supir_type" onchange="this.form.submit()" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none">
                <option value="">-- Semua Spesialisasi --</option>
                <option value="Haulage" {{ request('supir_type') == 'Haulage' ? 'selected' : '' }}>Haulage</option>
                <option value="LOLO" {{ request('supir_type') == 'LOLO' ? 'selected' : '' }}>LOLO</option>
                <option value="Penumpukan" {{ request('supir_type') == 'Penumpukan' ? 'selected' : '' }}>Penumpukan</option>
                <option value="TKBM" {{ request('supir_type') == 'TKBM' ? 'selected' : '' }}>TKBM</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-900 transition">
                Cari
            </button>
            @if(request()->anyFilled(['search', 'supir_type']))
                <a href="{{ route('supir.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">Reset</a>
            @endif
        </form>

        <button onclick="document.getElementById('modalCreateSupir').classList.remove('hidden')" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
            <i class="fa-solid fa-truck-front text-xs"></i>
            <span>Tambah Akun Supir Baru</span>
        </button>
    </div>

    <!-- Supir Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">Nama Driver</th>
                        <th class="py-3.5 px-6">Email Login (Mobile App)</th>
                        <th class="py-3.5 px-6">Spesialisasi / Tipe Supir</th>
                        <th class="py-3.5 px-6">No. Telepon / WhatsApp</th>
                        <th class="py-3.5 px-6">Tanggal Terdaftar</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supirs as $s)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-slate-800 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm border border-purple-200">
                                    <i class="fa-solid fa-id-card"></i>
                                </div>
                                <div>{{ $s->name }}</div>
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-blue-600 font-semibold">{{ $s->email }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase
                                    {{ $s->supir_type == 'Haulage' ? 'bg-purple-100 text-purple-700 border border-purple-200' : '' }}
                                    {{ $s->supir_type == 'LOLO' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                                    {{ $s->supir_type == 'Penumpukan' ? 'bg-amber-100 text-amber-700 border border-amber-200' : '' }}
                                    {{ $s->supir_type == 'TKBM' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}">
                                    {{ $s->supir_type }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">{{ $s->phone ?? '-' }}</td>
                            <td class="py-4 px-6 text-xs text-slate-500">
                                {{ $s->created_at->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6 text-right space-x-1">
                                <button onclick="editSupir({{ json_encode($s) }})" class="px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>

                                <form action="{{ route('supir.destroy', $s->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus akun supir ini?')">
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
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada akun supir terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $supirs->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah Supir -->
<div id="modalCreateSupir" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="font-bold text-lg text-slate-800">Tambah Akun Supir Baru</h3>
            <button onclick="document.getElementById('modalCreateSupir').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('supir.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Driver *</label>
                <input type="text" name="name" required placeholder="Supir Haulage 1" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Login Mobile App *</label>
                <input type="email" name="email" required placeholder="supir_haulage@bkj.com" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Spesialisasi / Tipe Supir *</label>
                <select name="supir_type" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="Haulage">Haulage</option>
                    <option value="LOLO">LOLO</option>
                    <option value="Penumpukan">Penumpukan</option>
                    <option value="TKBM">TKBM</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WhatsApp *</label>
                <input type="text" name="phone" required placeholder="081299001101" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Password *</label>
                <input type="password" name="password" required value="password" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalCreateSupir').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-600 font-semibold rounded-xl text-xs hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-blue-600/30 transition">
                    Simpan Akun Supir
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Supir -->
<div id="modalEditSupir" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="font-bold text-lg text-slate-800">Edit Akun Supir</h3>
            <button onclick="document.getElementById('modalEditSupir').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditSupir" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Driver *</label>
                <input type="text" id="editSupirName" name="name" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Login Mobile App *</label>
                <input type="email" id="editSupirEmail" name="email" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Spesialisasi / Tipe Supir *</label>
                <select id="editSupirType" name="supir_type" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="Haulage">Haulage</option>
                    <option value="LOLO">LOLO</option>
                    <option value="Penumpukan">Penumpukan</option>
                    <option value="TKBM">TKBM</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WhatsApp *</label>
                <input type="text" id="editSupirPhone" name="phone" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" placeholder="••••••••" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalEditSupir').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-600 font-semibold rounded-xl text-xs hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-amber-600/30 transition">
                    Update Akun Supir
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editSupir(s) {
        document.getElementById('formEditSupir').action = "/supir/" + s.id;
        document.getElementById('editSupirName').value = s.name;
        document.getElementById('editSupirEmail').value = s.email;
        document.getElementById('editSupirType').value = s.supir_type;
        document.getElementById('editSupirPhone').value = s.phone || '';
        document.getElementById('modalEditSupir').classList.remove('hidden');
    }
</script>
@endsection
