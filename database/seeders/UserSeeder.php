<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $librarian = User::create([
            'name' => 'Francisco Rauda',
            'email' => 'francisco@gmail.com',
            'password' => bcrypt('password')
        ]);
        $librarian->assignRole('librarian');

        $student = User::create([
            'name' => 'Mauricio Recinos',
            'email' => 'mauricio@gmail.com',
            'password' => bcrypt('password')
        ]);
        $student->assignRole('student');

        $teacher = User::create([
            'name' => 'Rodrigo Quijada',
            'email' => 'rodrigo@gmail.com',
            'password' => bcrypt('password')
        ]);
        $teacher->assignRole('teacher');
    }
}