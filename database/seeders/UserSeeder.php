<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            User::updateOrCreate(
                ['name' => "user{$i}"],
                [
                    'email' => "user{$i}@example.com",
                    'password' => Hash::make('password1@3'),
                ]
            );
        }
    }
}