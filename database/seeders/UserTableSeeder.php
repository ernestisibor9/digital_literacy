<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('users')->insert([
            // Admin
            [
                'firstname' => 'Admin',
                'lastname' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => Hash::make(111),
                'role' => 'admin',
            ],

            // User
            [
                'firstname' => 'User',
                'lastname' => 'UserMatana',
                'email' => 'user@gmail.com',
                'password' => Hash::make(111),
                'role' => 'user',
            ],

            // Instructor
            [
                'firstname' => 'Instructor',
                'lastname' => 'Instructino',
                'email' => 'instructor@gmail.com',
                'password' => Hash::make(111),
                'role' => 'instructor',
            ]
        ]);
    }
}
