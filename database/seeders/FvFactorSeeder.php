<?php

namespace Database\Seeders;

use App\Models\FvFactor;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk Tabel 2.4 (Koefisien Fv) SNI 1726:2019.
 * Kelas Situs F sengaja tidak diisi, sama alasannya seperti FaFactorSeeder.
 */
class FvFactorSeeder extends Seeder
{
    public function run(): void
    {
        FvFactor::truncate();

        $data = [
            // Kelas Situs A
            ['site_class' => 'A', 's1_min' => 0,      's1_max' => 0.1, 'fv_value' => 0.8],
            ['site_class' => 'A', 's1_min' => 0.1001, 's1_max' => 0.2, 'fv_value' => 0.8],
            ['site_class' => 'A', 's1_min' => 0.2001, 's1_max' => 0.3, 'fv_value' => 0.8],
            ['site_class' => 'A', 's1_min' => 0.3001, 's1_max' => 0.4, 'fv_value' => 0.8],
            ['site_class' => 'A', 's1_min' => 0.4001, 's1_max' => 0.5, 'fv_value' => 0.8],
            ['site_class' => 'A', 's1_min' => 0.5001, 's1_max' => 999, 'fv_value' => 0.8],

            // Kelas Situs B
            ['site_class' => 'B', 's1_min' => 0,      's1_max' => 0.1, 'fv_value' => 0.8],
            ['site_class' => 'B', 's1_min' => 0.1001, 's1_max' => 0.2, 'fv_value' => 0.8],
            ['site_class' => 'B', 's1_min' => 0.2001, 's1_max' => 0.3, 'fv_value' => 0.8],
            ['site_class' => 'B', 's1_min' => 0.3001, 's1_max' => 0.4, 'fv_value' => 0.8],
            ['site_class' => 'B', 's1_min' => 0.4001, 's1_max' => 0.5, 'fv_value' => 0.8],
            ['site_class' => 'B', 's1_min' => 0.5001, 's1_max' => 999, 'fv_value' => 0.8],

            // Kelas Situs C
            ['site_class' => 'C', 's1_min' => 0,      's1_max' => 0.1, 'fv_value' => 1.5],
            ['site_class' => 'C', 's1_min' => 0.1001, 's1_max' => 0.2, 'fv_value' => 1.5],
            ['site_class' => 'C', 's1_min' => 0.2001, 's1_max' => 0.3, 'fv_value' => 1.5],
            ['site_class' => 'C', 's1_min' => 0.3001, 's1_max' => 0.4, 'fv_value' => 1.5],
            ['site_class' => 'C', 's1_min' => 0.4001, 's1_max' => 0.5, 'fv_value' => 1.5],
            ['site_class' => 'C', 's1_min' => 0.5001, 's1_max' => 999, 'fv_value' => 1.4],

            // Kelas Situs D
            ['site_class' => 'D', 's1_min' => 0,      's1_max' => 0.1, 'fv_value' => 2.4],
            ['site_class' => 'D', 's1_min' => 0.1001, 's1_max' => 0.2, 'fv_value' => 2.2],
            ['site_class' => 'D', 's1_min' => 0.2001, 's1_max' => 0.3, 'fv_value' => 2.0],
            ['site_class' => 'D', 's1_min' => 0.3001, 's1_max' => 0.4, 'fv_value' => 1.9],
            ['site_class' => 'D', 's1_min' => 0.4001, 's1_max' => 0.5, 'fv_value' => 1.8],
            ['site_class' => 'D', 's1_min' => 0.5001, 's1_max' => 999, 'fv_value' => 1.7],

            // Kelas Situs E
            ['site_class' => 'E', 's1_min' => 0,      's1_max' => 0.1, 'fv_value' => 4.2],
            ['site_class' => 'E', 's1_min' => 0.1001, 's1_max' => 0.2, 'fv_value' => 3.3],
            ['site_class' => 'E', 's1_min' => 0.2001, 's1_max' => 0.3, 'fv_value' => 2.8],
            ['site_class' => 'E', 's1_min' => 0.3001, 's1_max' => 0.4, 'fv_value' => 2.4],
            ['site_class' => 'E', 's1_min' => 0.4001, 's1_max' => 0.5, 'fv_value' => 2.2],
            ['site_class' => 'E', 's1_min' => 0.5001, 's1_max' => 999, 'fv_value' => 2.0],
        ];

        foreach ($data as $row) {
            FvFactor::create($row);
        }
    }
}