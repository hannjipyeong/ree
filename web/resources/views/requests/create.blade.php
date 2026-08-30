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
                        @if($adminSource)
                            <div class="relative">
                                <select name="source" class="w-full py-2.5 px-3 bg-slate-100 border border-slate-300 rounded-xl text-sm font-bold text-slate-800 pointer-events-none cursor-not-allowed">
                                    <option value="{{ $adminSource }}" selected>{{ $adminSource }} (Terkunci Sesuai Akun)</option>
                                </select>
                                <input type="hidden" name="source" value="{{ $adminSource }}">
                            </div>
                            <p class="text-[11px] text-blue-600 mt-1 font-medium"><i class="fa-solid fa-lock text-[10px]"></i> Akun Anda dibatasi untuk membuat order dengan source: <strong>{{ $adminSource }}</strong></p>
                        @else
                            <select name="source" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <option value="ALL IN">ALL IN</option>
                                <option value="Koperasi">Koperasi</option>
                                <option value="PBM Lain">PBM Lain</option>
                                <option value="BKJ-PBM">BKJ-PBM</option>
                            </select>
                        @endif
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
                            <option value="Batu Ampar">Batu Ampar</option>
                            <option value="Selatan">Selatan</option>
                            <option value="Eximen">Eximen</option>
                            <option value="Utara">Utara</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Lokasi Fasilitas *</label>
                        <select name="lokasi_fasilitas" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="gudang">Gudang</option>
                            <option value="TPFT">TPFT</option>
                            <option value="CFS">CFS</option>
                            <option value="loss cargo">Loss Cargo</option>
                            <option value="tps">TPS</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Jenis Kegiatan *</label>
                        <input type="text" name="jenis_kegiatan" value="storage" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>
            </div>

            <!-- Section 2: Detail Muatan & Payload -->
            <div>
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-box text-blue-600"></i>
                    2. Detail Muatan & Dokumen
                </h3>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Tipe Payload *</label>
                        <select id="payload_type_select" name="payload_type" onchange="togglePayloadSections(this.value)" class="w-full md:w-1/2 py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="Container" selected>Container (Peti Kemas)</option>
                            <option value="Cargo">Cargo (General Cargo / Muatan Bebas)</option>
                        </select>
                    </div>

                    <!-- Container Section -->
                    <div id="container_section" class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <label class="block text-xs font-bold text-slate-700">Detail Kontainer 1</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <input type="text" name="containers[0][container_type]" value="20' GP" placeholder="Tipe (20' GP)" class="py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs">
                            <input type="text" name="containers[0][container_size]" value="20 ft" placeholder="Ukuran (20 ft)" class="py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs">
                            <input type="text" name="containers[0][container_number]" placeholder="No. Kontainer (ABCD 123456 7)" class="py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs">
                        </div>
                    </div>

                    <!-- Cargo Section -->
                    <div id="cargo_section" class="hidden p-5 bg-amber-50/50 border border-amber-200 rounded-xl space-y-4">
                        <div class="flex items-center gap-2 text-amber-800 font-bold text-sm border-b border-amber-200 pb-2">
                            <i class="fa-solid fa-boxes-packing"></i> Rincian Cargo & Manifest Dokumen
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Barang *</label>
                                <input type="text" name="jenis_barang" placeholder="Contoh: General Cargo / Pakaian Jadi" class="w-full py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Tonase (Ton) *</label>
                                <input type="number" step="0.1" name="jumlah_tonase" placeholder="Contoh: 5.2" class="w-full py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Container Cargo (Opsional)</label>
                                <input type="text" name="nomor_container_cargo" placeholder="Nomor kontainer jika ada" class="w-full py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Upload File Manifest Cargo (PDF / JPG / PNG) *</label>
                                <input type="file" name="cargo_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full py-2 px-3 bg-white border border-slate-200 rounded-lg text-xs file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
                                <p class="text-[11px] text-slate-500 mt-1">Dokumen ini otomatis menjadi Halaman 2 pada Cetak Surat Permohonan Cargo.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Section 3: Pilihan Layanan -->
            <div>
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-blue-600"></i>
                    3. Pilihan Jasa Layanan (Akan Diberikan ke Pelaksana Lapangan)
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox" name="services[]" value="Railing" checked class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-semibold text-slate-800">Railing</span>
                    </label>

                    <label class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox" name="services[]" value="LOLO" checked class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-semibold text-slate-800">LOLO</span>
                    </label>

                    <label class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox" name="services[]" value="Storage" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-semibold text-slate-800">Storage</span>
                    </label>

                    <label class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox" name="services[]" value="TKBM" id="service_tkbm" onchange="toggleTkbmOption()" class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm font-semibold text-slate-800">TKBM</span>
                    </label>
                </div>
            </div>

            <!-- Section 4: Layanan Tambahan -->
            <div>
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-blue-600"></i>
                    4. Layanan Tambahan (Extra Services / Non-Pelaksana Lapangan)
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

            <!-- Section: TKBM Option (Hidden by default) -->
            <div id="tkbm_option_section" class="hidden">
                <label class="block text-xs font-bold text-slate-700 mb-2">Opsi TKBM Khusus *</label>
                <select name="tkbm_option" class="w-full md:w-1/2 py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="Man Power" selected>Man Power</option>
                    <option value="Man Power + Forklift">Man Power + Forklift</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-lg shadow-blue-600/30 transition">
                    Simpan & Publish Order Request
                </button>
            </div>

        </form>
    </div>
</div>

<script>
function togglePayloadSections(type) {
    const containerSection = document.getElementById('container_section');
    const cargoSection = document.getElementById('cargo_section');
    if (type === 'Cargo') {
        containerSection.classList.add('hidden');
        cargoSection.classList.remove('hidden');
    } else {
        containerSection.classList.remove('hidden');
        cargoSection.classList.add('hidden');
    }
}
function toggleTkbmOption() {
    const tkbmCheckbox = document.getElementById('service_tkbm');
    const tkbmOptionSection = document.getElementById('tkbm_option_section');
    if (tkbmCheckbox && tkbmCheckbox.checked) {
        tkbmOptionSection.classList.remove('hidden');
    } else {
        tkbmOptionSection.classList.add('hidden');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    togglePayloadSections(document.getElementById('payload_type_select').value);
});
</script>
@endsection
