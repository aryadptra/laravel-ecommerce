<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = User::create([
            'name' => 'Arya Dwi Putra',
            'email' => 'arya@gmail.com',
            'password' => Hash::make('password')
        ]);
        if ($data) {
            UserDetails::create([
                'user_id' => $data['id'],
                'phone_number' => "089662164536",
                'avatar' => NULL,
                'country' => 'ID',
            ]);
        }
    }
}
