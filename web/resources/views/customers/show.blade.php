@extends('layouts.app')

@section('title', 'Detail Akun Customer — ' . $customer->name)
@section('page_heading', 'Detail Akun Customer')

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-sm transition">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Customer
        </a>
    </div>

    <!-- Customer Profile Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl font-black shadow-lg shadow-blue-600/30">
                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-extrabold text-slate-800">{{ $customer->name }}</h2>
                        <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded-full text-xs border border-emerald-200">
                            Customer
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Terdaftar sejak {{ $customer->created_at->format('d M Y, H:i') }}
                    </p>
                </div>
            </div>

            <!-- Summary Chips -->
            <div class="flex flex-wrap gap-3">
                <div class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Total Order</div>
                    <div class="text-lg font-black text-slate-800">{{ $totalOrders }}</div>
                </div>
                <div class="px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                    <div class="text-[10px] uppercase font-bold text-emerald-600">Selesai</div>
                    <div class="text-lg font-black text-emerald-700">{{ $doneOrders }}</div>
                </div>
                <div class="px-4 py-2 bg-amber-50 border border-amber-200 rounded-xl text-center">
                    <div class="text-[10px] uppercase font-bold text-amber-600">Proses</div>
                    <div class="text-lg font-black text-amber-700">{{ $inProgressOrders }}</div>
                </div>
                <div class="px-4 py-2 bg-blue-50 border border-blue-200 rounded-xl text-center">
                    <div class="text-[10px] uppercase font-bold text-blue-600">Pending</div>
                    <div class="text-lg font-black text-blue-700">{{ $pendingOrders }}</div>
                </div>
            </div>
        </div>

        <hr class="my-6 border-slate-100">

        <!-- Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 font-medium block mb-1"><i class="fa-solid fa-envelope me-1.5 text-blue-500"></i>Email Login</span>
                <span class="font-bold text-slate-800 text-sm truncate block">{{ $customer->email }}</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 font-medium block mb-1"><i class="fa-solid fa-phone me-1.5 text-emerald-500"></i>No. Telepon</span>
                <span class="font-bold text-slate-800 text-sm">{{ $customer->phone ?? '-' }}</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 font-medium block mb-1"><i class="fa-solid fa-building me-1.5 text-purple-500"></i>Default Nama PT</span>
                <span class="font-bold text-slate-800 text-sm">{{ $customer->default_nama_pt ?? '-' }}</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 font-medium block mb-1"><i class="fa-solid fa-shield-halved me-1.5 text-amber-500"></i>Default Asuransi</span>
                <span class="font-bold text-sm {{ $customer->has_default_asuransi ? 'text-emerald-600' : 'text-slate-500' }}">
                    {{ $customer->has_default_asuransi ? 'Aktif' : 'Tidak' }}
                </span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 font-medium block mb-1"><i class="fa-solid fa-file-contract me-1.5 text-teal-500"></i>Default SPK</span>
                <span class="font-bold text-sm {{ $customer->has_default_sp3kk ? 'text-emerald-600' : 'text-slate-500' }}">
                    {{ $customer->has_default_sp3kk ? 'Wajib / Aktif' : 'Tidak' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Customer Orders History -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-blue-600"></i>
                    Riwayat Order Permohonan ({{ $orders->total() }})
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar semua order yang diajukan oleh akun perusahaan ini.</p>
            </div>
        </div>

        <div class="table-responsive-touch overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-6">No. Order</th>
                        <th class="py-3.5 px-6">Source / Modul</th>
                        <th class="py-3.5 px-6">Tanggal Order</th>
                        <th class="py-3.5 px-6">Wilayah & Fasilitas</th>
                        <th class="py-3.5 px-6">Tipe Muatan</th>
                        <th class="py-3.5 px-6">Status Order</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $ord)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-blue-600">
                                <a href="{{ route('requests.show', $ord->id) }}" class="hover:underline">
                                    {{ $ord->order_number }}
                                </a>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold rounded-lg text-xs">
                                    {{ $ord->source }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-600">
                                {{ \Carbon\Carbon::parse($ord->tanggal_order)->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6 text-xs">
                                <div class="font-bold text-slate-800">{{ $ord->wilayah }}</div>
                                <div class="text-slate-400">{{ strtoupper($ord->lokasi_fasilitas) }}</div>
                            </td>
                            <td class="py-4 px-6 text-xs">
                                <span class="font-semibold text-slate-700">{{ $ord->payload_type ?? 'Container' }}</span>
                                @if($ord->containers && $ord->containers->count() > 0)
                                    <span class="text-slate-400 block">({{ $ord->containers->count() }} kontainer)</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $st = strtolower($ord->status);
                                @endphp
                                @if($st === 'done' || $st === 'selesai' || $st === 'completed')
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold">
                                        Done
                                    </span>
                                @elseif($st === 'in progress' || $st === 'in' || $st === 'out')
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-bold">
                                        Proses
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-xs font-bold">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('requests.show', $ord->id) }}" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-eye"></i> Detail Order
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                Customer ini belum memiliki riwayat order permohonan.
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
