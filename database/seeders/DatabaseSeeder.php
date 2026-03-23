<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(DefaultPagesTableSeeder::class);
        $this->call(DefaultUsersTableSeeder::class);
        $this->call(DefaultUsersRolesTableSeeder::class);
        $this->call(DefaultUsersColumnsSetTableSeeder::class);
        $this->call(DefaultUsersColumnsTableSeeder::class);
        $this->call(DefaultConfigsTableSeeder::class);
        $this->call(DefaultPluginsTableSeeder::class);
        $this->call(DefaultReservationsTableSeeder::class);
    }
}
