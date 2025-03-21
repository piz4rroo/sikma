<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Hapus semua data lama
        DB::table('users')->truncate();

        // Tambahkan data baru
        DB::table('users')->insert([
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'), // Gunakan Hash::make() agar aman
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Manusia',
                'email' => 'manusia@gmail.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
