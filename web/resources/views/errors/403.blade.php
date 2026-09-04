@extends('layouts.app')

@section('title', 'Akses Ditolak')
@section('page_heading', 'Akses Ditolak (403)')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[65vh] text-center px-4">
    <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mb-6 shadow-sm border border-red-200">
        <i class="fa-solid fa-ban text-4xl text-red-600"></i>
    </div>
    <h1 class="text-3xl font-extrabold text-slate-800 mb-3">Akses Ditolak</h1>
    <p class="text-slate-600 mb-8 max-w-md text-sm leading-relaxed">
        {{ $exception->getMessage() ?: 'Maaf, akun Anda tidak memiliki izin untuk mengakses halaman atau melakukan aksi ini. Hubungi administrator jika Anda merasa ini adalah sebuah kesalahan.' }}
    </p>
    <div class="flex flex-wrap items-center justify-center gap-3">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="px-6 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold rounded-xl transition shadow-sm flex items-center gap-2 text-sm">
            <i class="fa-solid fa-arrow-left text-[11px]"></i> Kembali ke Sebelumnya
        </a>
        <a href="{{ route('dashboard') }}" class="px-6 py-2.5 bg-[#1C325B] hover:bg-slate-900 text-white font-bold rounded-xl transition shadow flex items-center gap-2 text-sm">
            <i class="fa-solid fa-house text-[11px]"></i> Ke Dashboard Utama
        </a>
    </div>
</div>
@endsection
