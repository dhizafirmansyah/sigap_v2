<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Sampoerna Mild',
                'code' => 'SM001',
                'description' => 'Rokok mild berkualitas premium',
                'category' => 'mild',
                'target_production_per_day' => 10000.00,
                'quality_standards' => json_encode([
                    'weight_min' => 0.8,
                    'weight_max' => 1.2,
                    'length_min' => 83,
                    'length_max' => 87,
                    'circumference_min' => 24.2,
                    'circumference_max' => 25.8
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Djarum Super',
                'code' => 'DS001',
                'description' => 'Rokok kretek super berkualitas',
                'category' => 'kretek',
                'target_production_per_day' => 8000.00,
                'quality_standards' => json_encode([
                    'weight_min' => 0.9,
                    'weight_max' => 1.3,
                    'length_min' => 82,
                    'length_max' => 88,
                    'circumference_min' => 24.0,
                    'circumference_max' => 26.0
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marlboro Red',
                'code' => 'MR001',
                'description' => 'Rokok putih premium internasional',
                'category' => 'putih',
                'target_production_per_day' => 12000.00,
                'quality_standards' => json_encode([
                    'weight_min' => 0.7,
                    'weight_max' => 1.1,
                    'length_min' => 83,
                    'length_max' => 87,
                    'circumference_min' => 24.5,
                    'circumference_max' => 25.5
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gudang Garam Filter',
                'code' => 'GGF001',
                'description' => 'Rokok kretek filter tradisional',
                'category' => 'kretek',
                'target_production_per_day' => 9000.00,
                'quality_standards' => json_encode([
                    'weight_min' => 0.9,
                    'weight_max' => 1.4,
                    'length_min' => 81,
                    'length_max' => 89,
                    'circumference_min' => 23.8,
                    'circumference_max' => 26.2
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('brands')->insert($brands);
    }
}
