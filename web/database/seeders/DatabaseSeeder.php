<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderContainer;
use App\Models\SubTask;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with rich operational data.
     */
    public function run(): void
    {
        // 1. Admin Users (Super Admin + 3 Source Admins)
        $superAdmin = User::create([
            'name' => 'Super Admin BKJ Ops',
            'email' => 'admin@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081122334455',
            'role' => 'admin',
            'admin_source' => null,
        ]);

        $adminAllIn = User::create([
            'name' => 'Admin ALL IN',
            'email' => 'admin.allin@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081122334401',
            'role' => 'admin',
            'admin_source' => 'ALL IN',
        ]);

        $adminKoperasi = User::create([
            'name' => 'Admin Koperasi',
            'email' => 'admin.koperasi@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081122334402',
            'role' => 'admin',
            'admin_source' => 'Koperasi',
        ]);

        $adminPbmLain = User::create([
            'name' => 'Admin PBM Lain',
            'email' => 'admin.pbmlain@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081122334403',
            'role' => 'admin',
            'admin_source' => 'PBM Lain',
        ]);

        // 2. Customer Users
        $customer1 = User::create([
            'name' => 'PT. Transport Nusantara',
            'email' => 'customer@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role' => 'customer',
        ]);

        $customer2 = User::create([
            'name' => 'PT. Samudra Biru Utama',
            'email' => 'samudra@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081399887766',
            'role' => 'customer',
        ]);

        $customer3 = User::create([
            'name' => 'PT. Global Trans Logistik',
            'email' => 'globaltrans@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '085711223344',
            'role' => 'customer',
        ]);

        $customer4 = User::create([
            'name' => 'PT. Bahari Kargo Indonesia',
            'email' => 'bahari@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081800112233',
            'role' => 'customer',
        ]);

        // 3. Supir Accounts (Driver Operasional)
        $supirHaulage1 = User::create([
            'name' => 'Supir Haulage Utama',
            'email' => 'supir_haulage@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001101',
            'role' => 'supir',
            'supir_type' => 'Haulage',
        ]);

        $supirHaulage2 = User::create([
            'name' => 'Budi Santoso (Haulage 2)',
            'email' => 'budi_haulage@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001105',
            'role' => 'supir',
            'supir_type' => 'Haulage',
        ]);

        $supirLolo1 = User::create([
            'name' => 'Operator (Telly) Utama',
            'email' => 'operator_lolo@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001102',
            'role' => 'supir',
            'supir_type' => 'LOLO',
        ]);

        $supirLolo2 = User::create([
            'name' => 'Agus Hermawan (Operator LOLO 2)',
            'email' => 'agus_lolo@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001106',
            'role' => 'supir',
            'supir_type' => 'LOLO',
        ]);

        $supirPenumpukan1 = User::create([
            'name' => 'Admin Penumpukan Utama',
            'email' => 'admin_penumpukan@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001103',
            'role' => 'supir',
            'supir_type' => 'Penumpukan',
        ]);

        $supirTbkmSelatan = User::create([
            'name' => 'Koordinator TKBM Selatan',
            'email' => 'koordinator_tkbm_selatan@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001104',
            'role' => 'supir',
            'supir_type' => 'TKBM',
            'supir_wilayah' => 'Selatan',
        ]);

        $supirTbkmUtara = User::create([
            'name' => 'Koordinator TKBM Utara',
            'email' => 'koordinator_tkbm_utara@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001107',
            'role' => 'supir',
            'supir_type' => 'TKBM',
            'supir_wilayah' => 'Utara',
        ]);

        $supirTbkmEximen = User::create([
            'name' => 'Koordinator TKBM Eximen',
            'email' => 'koordinator_tkbm_eximen@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001108',
            'role' => 'supir',
            'supir_type' => 'TKBM',
            'supir_wilayah' => 'Eximen',
        ]);

        // 4. Sample Orders & Sub-Tasks

        // --- ORDER 1: ALL IN (Today - In Progress) ---
        $order1 = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-001',
            'customer_id' => $customer1->id,
            'source' => 'ALL IN',
            'tanggal_order' => now(),
            'nama_pt' => 'PT. Transport Nusantara',
            'nama_pbm' => 'PT Bintang Kepri Jaya',
            'no_telp' => '081234567890',
            'wilayah' => 'Selatan',
            'lokasi_fasilitas' => 'TPFT',
            'jenis_kegiatan' => 'cek fisik',
            'payload_type' => 'Container',
            'tkbm_option' => 'Man Power + Forklift',
            'has_asuransi' => true,
            'asuransi_value' => 75000000,
            'status' => 'In Progress',
            'created_at' => now(),
        ]);

        OrderContainer::create([
            'order_id' => $order1->id,
            'container_type' => "20' GP",
            'container_size' => '20 ft',
            'container_number' => 'TCKU 123456 7',
        ]);
        OrderContainer::create([
            'order_id' => $order1->id,
            'container_type' => "40' HC",
            'container_size' => '40 ft',
            'container_number' => 'SEGU 890123 4',
        ]);

        SubTask::create([
            'task_number' => 'REQ-1001-HAU',
            'order_id' => $order1->id,
            'service_type' => 'Haulage',
            'supir_id' => $supirHaulage1->id,
            'status' => 'In',
            'in_note' => 'Truk armada HAU-01 tiba di gerbang TPFT Selatan pukul 08:30',
            'in_photo_path' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=800&auto=format&fit=crop&q=80',
        ]);
        SubTask::create([
            'task_number' => 'REQ-1001-LOL',
            'order_id' => $order1->id,
            'service_type' => 'LOLO',
            'supir_id' => $supirLolo1->id,
            'status' => 'Masuk',
        ]);
        SubTask::create([
            'task_number' => 'REQ-1001-PEN',
            'order_id' => $order1->id,
            'service_type' => 'Penumpukan',
            'supir_id' => $supirPenumpukan1->id,
            'status' => 'Masuk',
        ]);

        // --- ORDER 2: Koperasi (Today - Submitted) ---
        $order2 = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-002',
            'customer_id' => $customer2->id,
            'source' => 'Koperasi',
            'tanggal_order' => now(),
            'nama_pt' => 'PT. Samudra Biru Utama',
            'nama_pbm' => 'PBM Bahari Mandiri',
            'no_telp' => '081399887766',
            'wilayah' => 'Eximen',
            'lokasi_fasilitas' => 'CFS',
            'jenis_kegiatan' => 'stripping / staffing',
            'payload_type' => 'Container',
            'tkbm_option' => 'Man Power',
            'status' => 'Submitted',
            'created_at' => now()->subMinutes(30),
        ]);

        OrderContainer::create([
            'order_id' => $order2->id,
            'container_type' => "20' RF",
            'container_size' => '20 ft',
            'container_number' => 'TGHU 556677 8',
        ]);

        SubTask::create([
            'task_number' => 'REQ-1002-TBK',
            'order_id' => $order2->id,
            'service_type' => 'TKBM',
            'supir_id' => $supirTbkmEximen->id,
            'status' => 'Masuk',
        ]);

        // --- ORDER 3: PBM Lain (Yesterday - Completed/Out) ---
        $order3 = Order::create([
            'order_number' => 'ORD-' . date('Ymd', strtotime('-1 day')) . '-003',
            'customer_id' => $customer3->id,
            'source' => 'PBM Lain',
            'tanggal_order' => now()->subDay(),
            'nama_pt' => 'PT. Global Trans Logistik',
            'nama_pbm' => 'PT. PBM Pelabuhan Utama',
            'no_telp' => '085711223344',
            'wilayah' => 'Utara',
            'lokasi_fasilitas' => 'TPS',
            'jenis_kegiatan' => 'penumpukan',
            'payload_type' => 'Container',
            'status' => 'Completed',
            'created_at' => now()->subDay(),
        ]);

        OrderContainer::create([
            'order_id' => $order3->id,
            'container_type' => "40' GP",
            'container_size' => '40 ft',
            'container_number' => 'MSKU 998877 1',
        ]);

        SubTask::create([
            'task_number' => 'REQ-1003-HAU',
            'order_id' => $order3->id,
            'service_type' => 'Haulage',
            'supir_id' => $supirHaulage2->id,
            'status' => 'Done',
            'in_note' => 'Masuk TPS jam 10:00',
            'in_photo_path' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=800&auto=format&fit=crop&q=80',
            'out_note' => 'Pengantaran selesai jam 14:00',
            'out_photo_path' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&auto=format&fit=crop&q=80',
        ]);
        SubTask::create([
            'task_number' => 'REQ-1003-LOL',
            'order_id' => $order3->id,
            'service_type' => 'LOLO',
            'supir_id' => $supirLolo2->id,
            'status' => 'Done',
            'in_note' => 'LOLO selesai jam 11:30',
        ]);

        // --- ORDER 4: ALL IN (2 Days ago - Out) ---
        $order4 = Order::create([
            'order_number' => 'ORD-' . date('Ymd', strtotime('-2 days')) . '-004',
            'customer_id' => $customer4->id,
            'source' => 'ALL IN',
            'tanggal_order' => now()->subDays(2),
            'nama_pt' => 'PT. Bahari Kargo Indonesia',
            'nama_pbm' => 'PT Bintang Kepri Jaya',
            'no_telp' => '081800112233',
            'wilayah' => 'Selatan',
            'lokasi_fasilitas' => 'CFS',
            'jenis_kegiatan' => 'stripping / staffing',
            'payload_type' => 'Container',
            'tkbm_option' => 'Man Power',
            'status' => 'In Progress',
            'created_at' => now()->subDays(2),
        ]);

        OrderContainer::create([
            'order_id' => $order4->id,
            'container_type' => "20' OT",
            'container_size' => '20 ft',
            'container_number' => 'CMAU 443322 9',
        ]);

        SubTask::create([
            'task_number' => 'REQ-1004-PEN',
            'order_id' => $order4->id,
            'service_type' => 'Penumpukan',
            'supir_id' => $supirPenumpukan1->id,
            'status' => 'Out',
            'in_note' => 'Penumpukan dimulai pukul 13:00',
            'out_note' => 'Penumpukan selesai, kontainer siap diangkut',
        ]);

        // --- ORDER 5: Koperasi (3 Days ago - Completed) ---
        $order5 = Order::create([
            'order_number' => 'ORD-' . date('Ymd', strtotime('-3 days')) . '-005',
            'customer_id' => $customer1->id,
            'source' => 'Koperasi',
            'tanggal_order' => now()->subDays(3),
            'nama_pt' => 'PT. Transport Nusantara',
            'nama_pbm' => 'PBM Samudra Jaya',
            'no_telp' => '081234567890',
            'wilayah' => 'Eximen',
            'lokasi_fasilitas' => 'loss cargo',
            'jenis_kegiatan' => 'penumpukan',
            'payload_type' => 'Container',
            'status' => 'Completed',
            'created_at' => now()->subDays(3),
        ]);

        OrderContainer::create([
            'order_id' => $order5->id,
            'container_type' => "40' FR",
            'container_size' => '40 ft',
            'container_number' => 'HAPAG 776655 0',
        ]);

        SubTask::create([
            'task_number' => 'REQ-1005-TBK',
            'order_id' => $order5->id,
            'service_type' => 'TKBM',
            'supir_id' => $supirTbkmEximen->id,
            'status' => 'Done',
            'in_note' => 'Bongkar muat Man Power selesai tepat waktu',
        ]);

        // --- ORDER 6: ALL IN (Cargo Payload - Penumpukan Gudang) ---
        $order6 = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-006',
            'customer_id' => $customer1->id,
            'source' => 'ALL IN',
            'tanggal_order' => now(),
            'nama_pt' => 'PT. Jaya Mandiri Logistik',
            'nama_pbm' => 'PT. Bintang Kepri Jaya',
            'no_telp' => '081198765432',
            'wilayah' => 'Selatan',
            'lokasi_fasilitas' => 'gudang',
            'jenis_kegiatan' => 'penumpukan',
            'payload_type' => 'Cargo',
            'jenis_barang' => 'Sparepart Mesin Industri & Heavy Tools',
            'jumlah_tonase' => 8.5,
            'nomor_container_cargo' => 'CRGO-BAM-9982',
            'cargo_file_path' => 'uploads/cargo/sample_manifest.jpg',
            'tkbm_option' => 'Man Power + Forklift',
            'has_asuransi' => true,
            'asuransi_value' => 120000000,
            'status' => 'In Progress',
            'created_at' => now()->subHours(2),
        ]);

        SubTask::create([
            'task_number' => 'REQ-1006-PEN',
            'order_id' => $order6->id,
            'service_type' => 'Penumpukan',
            'supir_id' => $supirPenumpukan1->id,
            'status' => 'In',
            'in_note' => 'Barang cargo telah masuk dan ditumpuk di Gudang Terminal Batu Ampar',
            'in_photo_path' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&auto=format&fit=crop&q=80',
        ]);
        SubTask::create([
            'task_number' => 'REQ-1006-TBK',
            'order_id' => $order6->id,
            'service_type' => 'TKBM',
            'supir_id' => $supirTbkmSelatan->id,
            'status' => 'Masuk',
        ]);

        // --- ORDER 7: Koperasi (Cargo Payload - Stripping / Staffing di CFS) ---
        $order7 = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-007',
            'customer_id' => $customer2->id,
            'source' => 'Koperasi',
            'tanggal_order' => now(),
            'nama_pt' => 'PT. Berkah Samudera Abadi',
            'nama_pbm' => 'PBM Bahari Mandiri',
            'no_telp' => '081288776655',
            'wilayah' => 'Utara',
            'lokasi_fasilitas' => 'TPFT',
            'jenis_kegiatan' => 'Inspeksi',
            'payload_type' => 'Cargo',
            'jenis_barang' => 'Tekstil & Garment Ekspor',
            'jumlah_tonase' => 14.2,
            'nomor_container_cargo' => 'CRGO-CFS-1104',
            'cargo_file_path' => 'uploads/cargo/sample_manifest.jpg',
            'tkbm_option' => 'Man Power',
            'has_asuransi' => false,
            'status' => 'Submitted',
            'created_at' => now()->subMinutes(45),
        ]);

        SubTask::create([
            'task_number' => 'REQ-1007-TBK',
            'order_id' => $order7->id,
            'service_type' => 'TKBM',
            'supir_id' => $supirTbkmUtara->id,
            'status' => 'Masuk',
        ]);
        // Loop through all SubTasks and create container progress if the order has containers
        $subTasks = SubTask::all();
        foreach ($subTasks as $st) {
            $containers = OrderContainer::where('order_id', $st->order_id)->get();
            foreach ($containers as $c) {
                \App\Models\SubTaskContainerProgress::create([
                    'sub_task_id' => $st->id,
                    'order_container_id' => $c->id,
                    'status' => $st->status === 'Done' ? 'Out' : ($st->status === 'In' ? 'In' : 'Pending'),
                    'in_note' => $st->in_note,
                    'in_photo_path' => $st->in_photo_path,
                    'in_time' => $st->status !== 'Masuk' ? now()->subHours(2) : null,
                    'out_note' => $st->out_note,
                    'out_photo_path' => $st->out_photo_path,
                    'out_time' => $st->status === 'Done' ? now() : null,
                ]);
            }
        }
    }
}
