<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Buat akun admin default. Tidak ada registrasi publik —
     * akun admin hanya dibuat lewat seeder atau modul Manajemen User.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@denah.id'],
            [
                'name' => 'Admin DENAH',
                'password' => Hash::make('admin12345'),
                'email_verified_at' => now(),
            ]
        );
    }
}
