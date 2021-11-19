<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DefaultPagesTableSeeder::class);
        $this->call(DefaultUsersTableSeeder::class);
        $this->call(DefaultUsersRolesTableSeeder::class);
        $this->call(DefaultConfigsTableSeeder::class);
        $this->call(DefaultPluginsTableSeeder::class);
        $this->call(DefaultReservationsManegeSeeder::class);
    }
}
