<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeContact;

class TypeContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeContact::create([
            'description' => 'Correo'
        ]);

        TypeContact::create([
            'description' => 'Teléfono'
        ]);
    }
}
