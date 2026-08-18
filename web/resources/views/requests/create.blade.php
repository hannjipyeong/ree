@extends('layouts.app')

@section('title', 'Buat Request Order Manual')
@section('page_heading', 'Formulir Request Order Manual')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('requests.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Monitoring
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-8">
        <form action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Section 1: Informasi Utama -->
            <div>
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-file-contract text-blue-600"></i>
                    1. Informasi Umum Order
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Source Formulir *</label>
                        <select name="source" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="ALL IN">ALL IN</option>
                            <option value="Koperasi">Koperasi</option>
                            <option value="PBM Lain">PBM Lain</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Tanggal Order *</label>
                        <input type="date" name="tanggal_order" value="{{ date('Y-m-d') }}" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Nama PT Pemesan *</label>
                        <input type="text" name="nama_pt" placeholder="PT. Transport Nusantara" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Nama PBM *</label>
                        <input type="text" name="nama_pbm" value="PT Bintang Kepri Jaya" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">No. Telepon / PIC *</label>
                        <input type="text" name="no_telp" placeholder="081234567890" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Wilayah Operasional *</label>
                        <select name="wilayah" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="Selatan">Selatan</option>
                            <option value="Eximen">Eximen</option>
                            <option value="Utara">Utara</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Lokasi Fasilitas *</label>
                        <select name="lokasi_fasilitas" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="TPFT">TPFT</option>
                            <option value="CFS">CFS</option>
                            <option value="loss cargo">Loss Cargo</option>
                            <option value="gudang">Gudang</option>
                            <option value="tps">TPS</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Jenis Kegiatan *</label>
                        <input type="text" name="jenis_kegiatan" value="cek fisik" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>
            </div>

            <!-- Section 2: Detail Muatan & Kontainer -->
            <div>
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-box text-blue-600"></i>
                    2. Detail Muatan & Kontainer
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Tipe Payload *</label>
                        <select name="payload_type" class="w-full md:w-1/2 py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                            <option value="Container">Container</option>
                            <option value="Cargo">Cargo</option>
                        </select>
                    </div>

                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <label class="block text-xs font-bold text-slate-700 mb-3">Detail Kontainer 1</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <input type="text" name="containers[0][container_type]" value="20' GP" placeholder="Tipe (20' GP)" class="py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs">
                            <input type="text" name="containers[0][container_size]" value="20 ft" placeholder="Ukuran (20 ft)" class="py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs">
                            <input type="text" name="containers[0][container_number]" placeholder="No. Kontainer (ABCD 123456 7)" class="py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Pilihan Layanan (Membuat SubTask Supir) -->
            <div>
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-blue-600"></i>
                    3. Pilihan Jasa Layanan (Akan Diberikan ke Supir)
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox" name="services[]" value="Haulage" checked class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-semibold text-slate-800">Haulage</span>
                    </label>

                    <label class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox" name="services[]" value="LOLO" checked class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-semibold text-slate-800">LOLO</span>
                    </label>

                    <label class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox" name="services[]" value="Penumpukan" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-semibold text-slate-800">Penumpukan</span>
                    </label>

                    <label class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox" name="services[]" value="TKBM" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-semibold text-slate-800">TKBM</span>
                    </label>
                </div>
            </div>

            <!-- Section 4: Layanan Tambahan (Additional Services / Non-Supir) -->
            <div>
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-blue-600"></i>
                    4. Layanan Tambahan (Extra Services / Non-Supir)
                </h3>

                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="has_asuransi" value="1" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-semibold text-slate-800">Gunakan Jasa Asuransi Cargo / Freight Protection</span>
                    </label>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Pertanggungan Asuransi (Rp)</label>
                        <input type="number" name="asuransi_value" placeholder="Contoh: 50000000" class="w-full md:w-1/2 py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-lg shadow-blue-600/30 transition">
                    Simpan & Publish Order Request
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
