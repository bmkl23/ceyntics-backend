<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'       => 'Super Admin',
            'email'      => 'admin@ceyntics.com',
            'password'   => 'Admin@1234',
            'role'       => 'admin',
            'is_active'  => true,
            'created_by' => null,
        ]);
    }
}