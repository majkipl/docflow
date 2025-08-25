<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all()->keyBy('name');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@docflow.local',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach($roles['admin']->id);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@docflow.local',
            'password' => Hash::make('password'),
        ]);
        $manager->roles()->attach($roles['manager']->id);

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@docflow.local',
            'password' => Hash::make('password'),
        ]);
        $employee->roles()->attach($roles['employee']->id);
    }
}
