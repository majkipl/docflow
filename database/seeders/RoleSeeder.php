<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            ['name' => 'admin', 'description' => 'Administrator systemu'],
            ['name' => 'manager', 'description' => 'Menedżer zatwierdzający dokumenty'],
            ['name' => 'employee', 'description' => 'Pracownik wgrywający dokumenty'],
        ]);
    }
}
