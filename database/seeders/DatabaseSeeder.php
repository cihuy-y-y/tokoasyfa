<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'asyfa',
            'username' => 'asyfa',
            'email' => 'asyfa@example.com',
            'password' => Hash::make('asyfa123'),
            'role' => 'owner',
            'status' => 'approved',
        ]);
        $this->call(ProductSeeder::class);
    }
}
