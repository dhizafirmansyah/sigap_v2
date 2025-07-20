<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Pabrik Utama',
                'code' => 'PU001',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius' => 100.00,
                'address' => 'Jl. Industri No. 1, Jakarta',
                'description' => 'Pabrik utama produksi rokok',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pabrik Cabang A',
                'code' => 'PCA001',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'radius' => 150.00,
                'address' => 'Jl. Raya Bandung No. 15, Bandung',
                'description' => 'Pabrik cabang A untuk produksi regional',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gudang Packaging',
                'code' => 'GP001',
                'latitude' => -6.2000,
                'longitude' => 106.8500,
                'radius' => 80.00,
                'address' => 'Jl. Packaging No. 5, Jakarta',
                'description' => 'Gudang khusus untuk packaging dan kemas',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quality Control Center',
                'code' => 'QCC001',
                'latitude' => -6.2100,
                'longitude' => 106.8400,
                'radius' => 50.00,
                'address' => 'Jl. Quality No. 10, Jakarta',
                'description' => 'Pusat quality control dan testing',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('locations')->insert($locations);
    }
}
