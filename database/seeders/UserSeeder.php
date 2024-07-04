<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Person;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $personAdmin = Person::create([
            'firstName' => 'Karina',
            'firstLastName' => 'Solorzano',
            'gender' => false,
            'type_person_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'userName' => 'karina1245',
            'confirmated' => 1,
            'person_id' => $personAdmin->id,
            'password' => Hash::make('123456'),
        ]);
    }
}
