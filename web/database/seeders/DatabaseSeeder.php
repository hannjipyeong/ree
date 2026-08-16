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
     * Seed the application's database.
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

        // 2. Customer User
        $customer = User::create([
            'name' => 'PT. Transport Nusantara',
            'email' => 'customer@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role' => 'customer',
        ]);

        // 3. Supir Accounts (as requested by user)
        $supirHaulage = User::create([
            'name' => 'Supir Haulage Utama',
            'email' => 'supir_haulage@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001101',
            'role' => 'supir',
            'supir_type' => 'Haulage',
        ]);

        $supirLolo = User::create([
            'name' => 'Supir LOLO Utama',
            'email' => 'supir_lolo@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001102',
            'role' => 'supir',
            'supir_type' => 'LOLO',
        ]);

        $supirPenumpukan = User::create([
            'name' => 'Supir Penumpukan Utama',
            'email' => 'supir_penumpukan@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001103',
            'role' => 'supir',
            'supir_type' => 'Penumpukan',
        ]);

        $supirTbkm = User::create([
            'name' => 'Supir TBKM Utama',
            'email' => 'supir_tbkm@bkj.com',
            'password' => Hash::make('password'),
            'phone' => '081299001104',
            'role' => 'supir',
            'supir_type' => 'TBKM',
        ]);

        // 4. Sample Order 1 (ALL IN)
        $order1 = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-001',
            'customer_id' => $customer->id,
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
        ]);

        OrderContainer::create([
            'order_id' => $order1->id,
            'container_type' => "20' GP",
            'container_size' => '20 ft',
            'container_number' => 'ABCD 123456 7',
        ]);

        OrderContainer::create([
            'order_id' => $order1->id,
            'container_type' => "40' HC",
            'container_size' => '40 ft',
            'container_number' => 'EFGH 890123 4',
        ]);

        // Sub Tasks for Order 1
        SubTask::create([
            'task_number' => 'REQ-' . time() . '-HAU',
            'order_id' => $order1->id,
            'service_type' => 'Haulage',
            'supir_id' => $supirHaulage->id,
            'status' => 'In',
            'in_note' => 'Truk tiba di gerbang TPFT Selatan jam 09:00',
        ]);

        SubTask::create([
            'task_number' => 'REQ-' . time() . '-LOL',
            'order_id' => $order1->id,
            'service_type' => 'LOLO',
            'supir_id' => $supirLolo->id,
            'status' => 'Masuk',
        ]);

        SubTask::create([
            'task_number' => 'REQ-' . time() . '-PEN',
            'order_id' => $order1->id,
            'service_type' => 'Penumpukan',
            'supir_id' => $supirPenumpukan->id,
            'status' => 'Masuk',
        ]);

        // 5. Sample Order 2 (Koperasi)
        $order2 = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-002',
            'customer_id' => $customer->id,
            'source' => 'Koperasi',
            'tanggal_order' => now(),
            'nama_pt' => 'PT. Logistik Jayatama',
            'nama_pbm' => 'PBM Bahari Mandiri',
            'no_telp' => '087711223344',
            'wilayah' => 'Eximen',
            'lokasi_fasilitas' => 'CFS',
            'jenis_kegiatan' => 'striping / staffing',
            'payload_type' => 'Container',
            'status' => 'Submitted',
        ]);

        OrderContainer::create([
            'order_id' => $order2->id,
            'container_type' => "20' RF",
            'container_size' => '20 ft',
            'container_number' => 'KLMN 556677 8',
        ]);

        SubTask::create([
            'task_number' => 'REQ-' . (time() + 1) . '-TBK',
            'order_id' => $order2->id,
            'service_type' => 'TBKM',
            'supir_id' => $supirTbkm->id,
            'status' => 'Masuk',
        ]);
    }
}
