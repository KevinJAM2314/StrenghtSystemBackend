<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeIdentification;

class TypeIdentificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeIdentification::create([
            'description' => 'Cédula',
        ]);

        TypeIdentification::create([
            'description' => 'Pasaporte',
        ]);

        TypeIdentification::create([
            'description' => 'Licencia de conducir',
        ]);
        
    }
}
