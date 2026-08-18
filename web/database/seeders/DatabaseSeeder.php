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
        // 1. Admin User
        $admin = User::create([
            'name' => 'Admin BKJ Ops',
            'email' => 'admin@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081122334455',
            'role' => 'admin',
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
            'name' => 'Supir LOLO Utama',
            'email' => 'supir_lolo@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001102',
            'role' => 'supir',
            'supir_type' => 'LOLO',
        ]);

        $supirLolo2 = User::create([
            'name' => 'Agus Hermawan (LOLO 2)',
            'email' => 'agus_lolo@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001106',
            'role' => 'supir',
            'supir_type' => 'LOLO',
        ]);

        $supirPenumpukan1 = User::create([
            'name' => 'Supir Penumpukan Utama',
            'email' => 'supir_penumpukan@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001103',
            'role' => 'supir',
            'supir_type' => 'Penumpukan',
        ]);

        $supirTbkm1 = User::create([
            'name' => 'Supir TBKM Utama',
            'email' => 'supir_tbkm@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001104',
            'role' => 'supir',
            'supir_type' => 'TBKM',
        ]);

        // 4. Sample Orders & Sub-Tasks

        // --- ORDER 1: ALL IN (Today - In Progress) ---
        $order1 = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-001',
            'customer_id' => $customer1->id,
            'source' => 'ALL IN',
            'tanggal_order' => now(),
            'nama_pt' => 'PT. Transport Nusantara',
            'nama_pbm' => 'PT. ABC',
            'no_telp' => '081234567890',
            'wilayah' => 'Selatan',
            'lokasi_fasilitas' => 'TPFT',
            'jenis_kegiatan' => 'cek fisik',
            'payload_type' => 'Container',
            'tbkm_option' => 'Man Power + Forklift',
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
            'jenis_kegiatan' => 'striping / staffing',
            'payload_type' => 'Container',
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
            'service_type' => 'TBKM',
            'supir_id' => $supirTbkm1->id,
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
            'nama_pbm' => 'PT. ABC',
            'no_telp' => '081800112233',
            'wilayah' => 'Selatan',
            'lokasi_fasilitas' => 'CFS',
            'jenis_kegiatan' => 'striping / staffing',
            'payload_type' => 'Container',
            'tbkm_option' => 'Man Power',
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
            'service_type' => 'TBKM',
            'supir_id' => $supirTbkm1->id,
            'status' => 'Done',
            'in_note' => 'Bongkar muat Man Power selesai tepat waktu',
        ]);
    }
}
