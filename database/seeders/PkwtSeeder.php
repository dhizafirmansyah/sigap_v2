<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PkwtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pkwts = [
            [
                'name' => 'PKWT 1 Tahun',
                'description' => 'Kontrak kerja waktu tertentu selama 1 tahun',
                'duration_months' => 12,
                'is_renewable' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PKWT 2 Tahun',
                'description' => 'Kontrak kerja waktu tertentu selama 2 tahun',
                'duration_months' => 24,
                'is_renewable' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PKWT 3 Tahun',
                'description' => 'Kontrak kerja waktu tertentu selama 3 tahun',
                'duration_months' => 36,
                'is_renewable' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PKWT Proyek',
                'description' => 'Kontrak kerja untuk proyek khusus',
                'duration_months' => 6,
                'is_renewable' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('pkwts')->insert($pkwts);
    }
}
