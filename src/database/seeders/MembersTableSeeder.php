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
        $param = [
            'name' => 'スタッフ1',
            'email' => 'staff1@test.com',
            'password' => 'password',
        ];
        Member::create($param);

        $param = [
            'name' => 'スタッフ2',
            'email' => 'staff2@test.com',
            'password' => 'password',
        ];
        Member::create($param);
    }
}
