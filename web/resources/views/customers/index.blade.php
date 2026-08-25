@extends('layouts.app')

@section('title', 'Monitoring & CRUD Akun Customer')
@section('page_heading', 'Monitoring & CRUD Akun Customer (Perusahaan Pemesan)')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Search -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row gap-3 sm:gap-4 items-stretch sm:items-center justify-between">
        
        <form method="GET" action="{{ route('customers.index') }}" class="flex items-center gap-2 sm:gap-3 flex-1 sm:max-w-md">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama PT / Email / HP..." 
                    class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
            </div>

            <button type="submit" class="px-3 sm:px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-900 transition shrink-0">
                Cari
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('customers.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium shrink-0">Reset</a>
            @endif
        </form>

        <button onclick="document.getElementById('modalCreateCustomer').classList.remove('hidden')" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-blue-600/30 transition flex items-center justify-center gap-2 shrink-0">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>Tambah Akun Customer</span>
        </button>
    </div>

    <!-- Customer Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="table-responsive-touch overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">Nama Perusahaan / PT</th>
                        <th class="py-3.5 px-6">Email Login</th>
                        <th class="py-3.5 px-6">No. Telepon / WhatsApp</th>
                        <th class="py-3.5 px-6">Role</th>
                        <th class="py-3.5 px-6">Tanggal Terdaftar</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-slate-800 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <div>{{ $c->name }}</div>
                            </td>
                            <td class="py-4 px-6 text-slate-600">{{ $c->email }}</td>
                            <td class="py-4 px-6 text-slate-600 font-medium">{{ $c->phone ?? '-' }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-semibold rounded-full text-xs border border-emerald-200">
                                    Customer
                                </span>
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500">
                                {{ $c->created_at->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6 text-right space-x-1">
                                <a href="{{ route('customers.show', $c->id) }}" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition inline-block">
                                    <i class="fa-solid fa-circle-info me-1"></i> Detail
                                </a>

                                <button onclick="editCustomer({{ json_encode($c) }})" class="px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>

                                <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus akun customer ini?')">
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
                                Belum ada akun customer terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $customers->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah Customer -->
<div id="modalCreateCustomer" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="font-bold text-lg text-slate-800">Tambah Akun Customer Baru</h3>
            <button onclick="document.getElementById('modalCreateCustomer').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perusahaan / PT *</label>
                <input type="text" name="name" required placeholder="PT. Transport Nusantara" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Login *</label>
                <input type="email" name="email" required placeholder="customer@bkj.com" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WhatsApp *</label>
                <input type="text" name="phone" required placeholder="081234567890" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Password *</label>
                <input type="password" name="password" required value="password" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="border-t border-slate-100 pt-4 mt-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">Default Nama PT (Opsional)</label>
                <input type="text" name="default_nama_pt" placeholder="PT. Bawaan Untuk Order" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="flex items-center gap-2 mt-2">
                <input type="checkbox" name="has_default_asuransi" id="asuransiCreate" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-600">
                <label for="asuransiCreate" class="text-sm font-medium text-slate-700">Otomatis Centang Asuransi saat Order</label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalCreateCustomer').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-600 font-semibold rounded-xl text-xs hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-blue-600/30 transition">
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Customer -->
<div id="modalEditCustomer" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="font-bold text-lg text-slate-800">Edit Akun Customer</h3>
            <button onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditCustomer" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perusahaan / PT *</label>
                <input type="text" id="editName" name="name" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Login *</label>
                <input type="email" id="editEmail" name="email" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WhatsApp *</label>
                <input type="text" id="editPhone" name="phone" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" placeholder="••••••••" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="border-t border-slate-100 pt-4 mt-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">Default Nama PT (Opsional)</label>
                <input type="text" id="editDefaultNamaPt" name="default_nama_pt" placeholder="PT. Bawaan Untuk Order" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="flex items-center gap-2 mt-2">
                <input type="checkbox" id="editHasDefaultAsuransi" name="has_default_asuransi" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-600">
                <label for="editHasDefaultAsuransi" class="text-sm font-medium text-slate-700">Otomatis Centang Asuransi saat Order</label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-600 font-semibold rounded-xl text-xs hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-amber-600/30 transition">
                    Update Akun
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editCustomer(c) {
        document.getElementById('formEditCustomer').action = "/customers/" + c.id;
        document.getElementById('editName').value = c.name;
        document.getElementById('editEmail').value = c.email;
        document.getElementById('editPhone').value = c.phone || '';
        document.getElementById('editDefaultNamaPt').value = c.default_nama_pt || '';
        document.getElementById('editHasDefaultAsuransi').checked = c.has_default_asuransi == 1;
        document.getElementById('modalEditCustomer').classList.remove('hidden');
    }
</script>
@endsection
