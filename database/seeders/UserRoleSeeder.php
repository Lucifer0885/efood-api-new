<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Searching for user with email admin@test.com...');
        $user = User::whereEmail( 'admin@test.com')->first();
        if (!$user) {
            $this->command->error('User not found!');
            return;
        }
        $this->command->info("User found: {$user->name}");
        $this->command->info('Searching for role with name admin...');
        $role = Role::where('name->en','Admin')->first();
        if (!$role) {
            $this->command->error('Role not found!');
            return;
        }
        $this->command->info("Role found: {$role->name}");
        $this->command->info('Attaching role to user...');
        $user->roles()->attach($role->id);
    }
}
