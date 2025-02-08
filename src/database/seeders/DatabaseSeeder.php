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
        $this->call(MembersTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
        $this->call(AttendanceTableSeeder::class);
    }
}
