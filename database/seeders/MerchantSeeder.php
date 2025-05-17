<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Enums\RoleCode;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $merhcant = User::whereEmail('merchant@test.com')->first();
        $merchantRole = Role::whereId(RoleCode::merchant)->first();

        if ($merhcant && $merchantRole) {
            $merhcant->roles()->attach(RoleCode::merchant);
        }
    }
}
