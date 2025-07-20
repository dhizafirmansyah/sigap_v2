<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contracts = [
            [
                'name' => 'Kontrak Tetap (Permanent)',
                'description' => 'Kontrak kerja untuk karyawan tetap dengan benefit penuh',
                'type' => 'permanent',
                'base_salary' => 5000000.00,
                'benefits' => json_encode([
                    'tunjangan_transport' => 500000,
                    'tunjangan_makan' => 300000,
                    'asuransi_kesehatan' => true,
                    'jamsostek' => true,
                    'cuti_tahunan' => 12
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kontrak Sementara (Contract)',
                'description' => 'Kontrak kerja waktu tertentu untuk proyek atau periode tertentu',
                'type' => 'contract',
                'base_salary' => 4500000.00,
                'benefits' => json_encode([
                    'tunjangan_transport' => 400000,
                    'tunjangan_makan' => 250000,
                    'asuransi_kesehatan' => true,
                    'jamsostek' => true,
                    'cuti_tahunan' => 6
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Masa Percobaan (Probation)',
                'description' => 'Kontrak untuk karyawan dalam masa percobaan 3 bulan',
                'type' => 'probation',
                'base_salary' => 4000000.00,
                'benefits' => json_encode([
                    'tunjangan_transport' => 300000,
                    'tunjangan_makan' => 200000,
                    'asuransi_kesehatan' => false,
                    'jamsostek' => false,
                    'cuti_tahunan' => 0
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Magang (Internship)',
                'description' => 'Kontrak untuk mahasiswa atau fresh graduate magang',
                'type' => 'internship',
                'base_salary' => 2000000.00,
                'benefits' => json_encode([
                    'tunjangan_transport' => 200000,
                    'tunjangan_makan' => 150000,
                    'asuransi_kesehatan' => false,
                    'jamsostek' => false,
                    'cuti_tahunan' => 0
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('employee_contracts')->insert($contracts);
    }
}
