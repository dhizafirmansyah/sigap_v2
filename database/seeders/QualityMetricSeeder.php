<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QualityMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $qualityMetrics = [
            [
                'name' => 'Visual Quality - Kulit',
                'category' => 'visual_quality',
                'description' => 'Kualitas visual kulit rokok',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Visual Quality - Batang',
                'category' => 'visual_quality', 
                'description' => 'Kualitas visual batang rokok',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Filter Weight (FW)',
                'category' => 'fw',
                'description' => 'Berat filter rokok',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ventilation Firmness Index (VFI)',
                'category' => 'vfi',
                'description' => 'Indeks ketegasan ventilasi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Circumference',
                'category' => 'physical',
                'description' => 'Keliling rokok',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Length',
                'category' => 'physical',
                'description' => 'Panjang rokok',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Weight',
                'category' => 'physical',
                'description' => 'Berat rokok',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('quality_metrics')->insert($qualityMetrics);
    }
}
