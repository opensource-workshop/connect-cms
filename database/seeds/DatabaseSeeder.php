<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DefaultPagesTableSeeder::class);
        $this->call(DefaultUsersTableSeeder::class);
        $this->call(DefaultUsersRolesTableSeeder::class);
        $this->call(DefaultConfigsTableSeeder::class);
    }
}
