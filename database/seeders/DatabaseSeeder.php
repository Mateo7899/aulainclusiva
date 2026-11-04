<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RetoSeeder::class,
            ConsejoSeeder::class,
            InsigniaSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
