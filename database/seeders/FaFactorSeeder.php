<?php

namespace Database\Seeders;

use App\Models\FaFactor;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk Tabel 2.3 (Koefisien Fa) SNI 1726:2019.
 *
 * Kelas Situs F sengaja TIDAK diisi -- menurut SNI 1726:2019, kelas ini
 * ditandai "SS" (memerlukan investigasi geoteknik spesifik lokasi),
 * bukan nilai koefisien umum. Sistem akan otomatis menampilkan peringatan
 * "diperlukan penghitungan lebih mendetail" untuk kelas ini karena
 * lookup akan mengembalikan null (tidak ada baris yang cocok).
 */
class FaFactorSeeder extends Seeder
{
    public function run(): void
    {
        FaFactor::truncate();

        $data = [
            // Kelas Situs A
            ['site_class' => 'A', 'ss_min' => 0,      'ss_max' => 0.25, 'fa_value' => 0.8],
            ['site_class' => 'A', 'ss_min' => 0.2501, 'ss_max' => 0.5,  'fa_value' => 0.8],
            ['site_class' => 'A', 'ss_min' => 0.5001, 'ss_max' => 0.75, 'fa_value' => 0.8],
            ['site_class' => 'A', 'ss_min' => 0.7501, 'ss_max' => 1.0,  'fa_value' => 0.8],
            ['site_class' => 'A', 'ss_min' => 1.0001, 'ss_max' => 1.25, 'fa_value' => 0.8],
            ['site_class' => 'A', 'ss_min' => 1.2501, 'ss_max' => 999,  'fa_value' => 0.8],

            // Kelas Situs B
            ['site_class' => 'B', 'ss_min' => 0,      'ss_max' => 0.25, 'fa_value' => 0.9],
            ['site_class' => 'B', 'ss_min' => 0.2501, 'ss_max' => 0.5,  'fa_value' => 0.9],
            ['site_class' => 'B', 'ss_min' => 0.5001, 'ss_max' => 0.75, 'fa_value' => 0.9],
            ['site_class' => 'B', 'ss_min' => 0.7501, 'ss_max' => 1.0,  'fa_value' => 0.9],
            ['site_class' => 'B', 'ss_min' => 1.0001, 'ss_max' => 1.25, 'fa_value' => 0.9],
            ['site_class' => 'B', 'ss_min' => 1.2501, 'ss_max' => 999,  'fa_value' => 0.9],

            // Kelas Situs C
            ['site_class' => 'C', 'ss_min' => 0,      'ss_max' => 0.25, 'fa_value' => 1.3],
            ['site_class' => 'C', 'ss_min' => 0.2501, 'ss_max' => 0.5,  'fa_value' => 1.3],
            ['site_class' => 'C', 'ss_min' => 0.5001, 'ss_max' => 0.75, 'fa_value' => 1.2],
            ['site_class' => 'C', 'ss_min' => 0.7501, 'ss_max' => 1.0,  'fa_value' => 1.2],
            ['site_class' => 'C', 'ss_min' => 1.0001, 'ss_max' => 1.25, 'fa_value' => 1.2],
            ['site_class' => 'C', 'ss_min' => 1.2501, 'ss_max' => 999,  'fa_value' => 1.2],

            // Kelas Situs D
            ['site_class' => 'D', 'ss_min' => 0,      'ss_max' => 0.25, 'fa_value' => 1.6],
            ['site_class' => 'D', 'ss_min' => 0.2501, 'ss_max' => 0.5,  'fa_value' => 1.4],
            ['site_class' => 'D', 'ss_min' => 0.5001, 'ss_max' => 0.75, 'fa_value' => 1.2],
            ['site_class' => 'D', 'ss_min' => 0.7501, 'ss_max' => 1.0,  'fa_value' => 1.1],
            ['site_class' => 'D', 'ss_min' => 1.0001, 'ss_max' => 1.25, 'fa_value' => 1.0],
            ['site_class' => 'D', 'ss_min' => 1.2501, 'ss_max' => 999,  'fa_value' => 1.0],

            // Kelas Situs E
            ['site_class' => 'E', 'ss_min' => 0,      'ss_max' => 0.25, 'fa_value' => 2.4],
            ['site_class' => 'E', 'ss_min' => 0.2501, 'ss_max' => 0.5,  'fa_value' => 1.7],
            ['site_class' => 'E', 'ss_min' => 0.5001, 'ss_max' => 0.75, 'fa_value' => 1.3],
            ['site_class' => 'E', 'ss_min' => 0.7501, 'ss_max' => 1.0,  'fa_value' => 1.1],
            ['site_class' => 'E', 'ss_min' => 1.0001, 'ss_max' => 1.25, 'fa_value' => 0.9],
            ['site_class' => 'E', 'ss_min' => 1.2501, 'ss_max' => 999,  'fa_value' => 0.8],
        ];

        foreach ($data as $row) {
            FaFactor::create($row);
        }
    }
}