<?php

namespace Database\Seeders;

use App\Models\Part;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parts = [
            // Kategori: Oli
            ['name' => 'Oli Yamalube Super Sport 1L', 'sku' => 'OLI-YML-SS', 'buy_price' => 70000, 'sell_price' => 85000, 'stock' => 24, 'type' => 'Part'],
            ['name' => 'Oli Shell Advance AX7 1L', 'sku' => 'OLI-SHL-AX7', 'buy_price' => 55000, 'sell_price' => 65000, 'stock' => 36, 'type' => 'Part'],
            ['name' => 'Oli Motul 3100 Gold 1L', 'sku' => 'OLI-MTL-3100', 'buy_price' => 85000, 'sell_price' => 105000, 'stock' => 12, 'type' => 'Part'],
            ['name' => 'Oli Gardan Yamalube 150ml', 'sku' => 'OLI-GRD-YML', 'buy_price' => 12000, 'sell_price' => 17000, 'stock' => 50, 'type' => 'Part'],

            // Kategori: Busi
            ['name' => 'Busi NGK CPR9EA-9', 'sku' => 'BSI-NGK-CPR9', 'buy_price' => 18000, 'sell_price' => 25000, 'stock' => 40, 'type' => 'Part'],
            ['name' => 'Busi Denso U27EPR-N9', 'sku' => 'BSI-DNS-U27', 'buy_price' => 15000, 'sell_price' => 22000, 'stock' => 40, 'type' => 'Part'],

            // Kategori: Kampas Rem
            ['name' => 'Kampas Rem Depan Honda (Cakram)', 'sku' => 'KMP-DPN-HND', 'buy_price' => 40000, 'sell_price' => 55000, 'stock' => 20, 'type' => 'Part'],
            ['name' => 'Kampas Rem Belakang Yamaha (Tromol)', 'sku' => 'KMP-BLK-YMH', 'buy_price' => 35000, 'sell_price' => 50000, 'stock' => 25, 'type' => 'Part'],

            // Kategori: Filter
            ['name' => 'Filter Udara Honda Vario 125/150', 'sku' => 'FLT-UDR-VR', 'buy_price' => 30000, 'sell_price' => 45000, 'stock' => 15, 'type' => 'Part'],
            ['name' => 'Filter Oli Yamaha Vixion', 'sku' => 'FLT-OLI-VX', 'buy_price' => 25000, 'sell_price' => 35000, 'stock' => 20, 'type' => 'Part'],

            // Kategori: CVT & Cairan
            ['name' => 'V-Belt Honda Beat FI', 'sku' => 'VBL-HND-BT', 'buy_price' => 110000, 'sell_price' => 135000, 'stock' => 10, 'type' => 'Part'],
            ['name' => 'Air Radiator (Coolant) Honda 1L', 'sku' => 'RAD-HND-1L', 'buy_price' => 35000, 'sell_price' => 45000, 'stock' => 30, 'type' => 'Part'],
            ['name' => 'Minyak Rem Jumbo 50ml', 'sku' => 'MRM-JMB-50', 'buy_price' => 8000, 'sell_price' => 15000, 'stock' => 50, 'type' => 'Part'],
            
            // JASA (Service) - Biasanya bengkel punya kode jasa terpisah agar bisa di-scan atau dipilih
            ['name' => 'Jasa Servis Ringan / Tune Up', 'sku' => 'JAS-SRV-RGN', 'buy_price' => 0, 'sell_price' => 50000, 'stock' => 999, 'type' => 'Service'],
            ['name' => 'Jasa Servis CVT', 'sku' => 'JAS-SRV-CVT', 'buy_price' => 0, 'sell_price' => 45000, 'stock' => 999, 'type' => 'Service'],
            ['name' => 'Jasa Ganti Oli', 'sku' => 'JAS-GNT-OLI', 'buy_price' => 0, 'sell_price' => 10000, 'stock' => 999, 'type' => 'Service'],
        ];

        foreach ($parts as $part) {
            // Gunakan updateOrCreate agar data tidak ganda jika Seeder dijalankan dua kali
            Part::updateOrCreate(
                ['sku' => $part['sku']], 
                $part
            );
        }
    }
}
