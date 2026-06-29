<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // Removed WithoutModelEvents to allow wallet creation via User model events

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ExpenseCategorySeeder::class
        ]);
    }
}
