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
        $members = [];

        for ($i = 1; $i <= 10; $i++) {
            $members[] = [
                'name' => "スタッフ{$i}",
                'email' => "staff{$i}@test.com",
                'password' => 'password',
            ];
        }

        foreach ($members as $member) {
            Member::create($member);
        }
    }
}
