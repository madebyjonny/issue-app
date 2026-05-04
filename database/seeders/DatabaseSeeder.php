<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('SEED_USER_EMAIL')],
            [
                'name'     => env('SEED_USER_NAME'),
                'password' => bcrypt(env('SEED_USER_PASSWORD')),
            ]
        );
    }
}
