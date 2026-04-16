<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/UserSeeder.php

public function run(): void
{
    User::create([
        'name' => 'Jose',
        'email' => 'admin@test.com',
        'password' => bcrypt('Password!23')
    ]);

    User::create([
        'name' => 'User 1',
        'email' => 'user1@test.com',
        'password' => bcrypt('123456')
    ]);

    User::create([
        'name' => 'User 2',
        'email' => 'user2@test.com',
        'password' => bcrypt('123456')
    ]);
}
}
