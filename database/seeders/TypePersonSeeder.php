<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypePerson;

class TypePersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypePerson::create([
            'description' => 'Administrador',
        ]);

        TypePerson::create([
            'description' => 'Cliente',
        ]);
    }
}
