<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Member;

class MembersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            ['name' => 'スタッフ1', 'email' => 'staff1@test.com', 'password' => 'password'],
            ['name' => 'スタッフ2', 'email' => 'staff2@test.com', 'password' => 'password'],
            ['name' => 'スタッフ3', 'email' => 'staff3@test.com', 'password' => 'password'],
            ['name' => 'スタッフ4', 'email' => 'staff4@test.com', 'password' => 'password'],
            ['name' => 'スタッフ5', 'email' => 'staff5@test.com', 'password' => 'password'],
        ];

        foreach ($members as $member) {
            Member::create($member);
        }
    }
}
