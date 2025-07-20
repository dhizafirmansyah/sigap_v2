<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            QualityMetricSeeder::class,
            ShiftSeeder::class,
            LocationSeeder::class,
            BrandSeeder::class,
            PkwtSeeder::class,
            EmployeeSeeder::class,
            EmployeeContractSeeder::class,
        ]);
    }
}
