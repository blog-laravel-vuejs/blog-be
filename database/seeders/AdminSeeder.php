<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert([
            'name' => 'Trần Thị Kim Tiến',
            'email' => 'kimtientran0410@gmail.com',
            'password' => Hash::make('kimtientran0410'),
            'role' => 'admin',
            'avatar' => null,
            'token_verify_email' => null,
            'email_verified_at' => now(),
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);
    }
}
