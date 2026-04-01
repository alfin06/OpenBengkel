<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // SPAREPARTS
            [
                'name' => 'Oli Shell Helix HX7 10W-40 (1L)',
                'sku' => '5011914844391',
                'buy_price' => 75000,
                'sell_price' => 95000,
                'stock' => 24,
                'type' => 'Part',
            ],
            [
                'name' => 'Filter Oli Toyota Avanza/Innova',
                'sku' => '15601-BZ010',
                'buy_price' => 28000,
                'sell_price' => 45000,
                'stock' => 10,
                'type' => 'Part',
            ],
            [
                'name' => 'Kampas Rem Depan Honda Brio',
                'sku' => '45022-TG0-T00',
                'buy_price' => 210000,
                'sell_price' => 285000,
                'stock' => 4,
                'type' => 'Part',
            ],
            [
                'name' => 'Aki GS Astra Maintenance Free NS40Z',
                'sku' => 'GS-NS40Z-MF',
                'buy_price' => 650000,
                'sell_price' => 820000,
                'stock' => 3,
                'type' => 'Part',
            ],

            // SERVICES (JASA)
            [
                'name' => 'Jasa Ganti Oli Standard',
                'sku' => 'SERVICE-OIL',
                'buy_price' => 0,
                'sell_price' => 35000,
                'stock' => 0,
                'type' => 'Service',
            ],
            [
                'name' => 'Jasa Tune Up Mesin 4-Silinder',
                'sku' => 'SERVICE-TUNEUP',
                'buy_price' => 0,
                'sell_price' => 150000,
                'stock' => 0,
                'type' => 'Service',
            ],
            [
                'name' => 'Jasa Cuci Salju & Vacuum',
                'sku' => 'SERVICE-WASH',
                'buy_price' => 0,
                'sell_price' => 50000,
                'stock' => 0,
                'type' => 'Service',
            ],
        ];

        foreach ($data as $item) {
            \App\Models\Part::create($item);
        }
    }
}
