<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Person;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Person::create([
            'firstName' => 'Daniel',
            'secondName' => 'José',
            'firstLastName' => 'Vargas',
            'secondLastName' => 'Corella',
            'gender' => true,
            'dateBirth' => '2002/04/02',
            'type_person_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Person::create([
            'firstName' => 'Kevin',
            'firstLastName' => 'Arroyo',
            'secondLastName' => 'Mora',
            'gender' => true,
            'type_person_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
