<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => [
                'en' => 'admin',
                'el' => 'Διαχειριστής'
            ],
        ]);

        Role::create([
            'name' => [
                'en' => 'merchant',
                'el' => 'Έμπορος'
            ],
        ]);

        Role::create([
            'name' => [
                'en' => 'driver',
                'el' => 'Οδηγός'
            ],
        ]);
    }
}
