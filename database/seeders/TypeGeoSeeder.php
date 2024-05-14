<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeGeo;

class TypeGeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeGeo::create([
            'description' => 'Provincia',
        ]);

        TypeGeo::create([
            'description' => 'Canton',
        ]);

        TypeGeo::create([
            'description' => 'Distrito',
        ]);
    }
}
