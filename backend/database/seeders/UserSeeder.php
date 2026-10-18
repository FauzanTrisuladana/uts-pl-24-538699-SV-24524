<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Fauzan Trisuladana',
            'email' => 'fauzantrisuladana@gmail.com',
            'role' => 'bendahara',
            'status' => 'Aktif',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'activated_at' => now(),
        ]);
    }
}
