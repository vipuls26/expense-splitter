<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Vipul',
            'email' => 'vipul@gmail.com',
            'password' => bcrypt('password'),
            'phone_no' => '9876543211',
        ]);

        User::create([
            'name' => 'Sam',
            'email' => 'sam@gmail.com',
            'password' => bcrypt('password'),
            'phone_no' => '7894561233',
        ]);

        User::create([
            'name' => 'Jhon',
            'email' => 'jhon@gmail.com',
            'password' => bcrypt('password'),
            'phone_no' => '7894561232',
        ]);

        User::create([
            'name' => 'Alex',
            'email' => 'alex@gmail.com',
            'password' => bcrypt('password'),
            'phone_no' => '7894561231',
        ]);

        User::create([
            'name' => 'Sammer',
            'email' => 'sammer@gmail.com',
            'password' => bcrypt('password'),
            'phone_no' => '7894561234',
        ]);
    }
}
