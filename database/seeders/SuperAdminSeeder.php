<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        $user = User::updateOrCreate(
            ['email' => 'masterloren@gmail.com'],
            [
                'name' => 'Master',
                'password' => Hash::make('master'),
                'email_verified_at' => now(),
            ]
        );

        // Use existing Super Admin role (with space)
        $role = Role::where('name', 'Super Admin')->first();
        
        if (!$role) {
            $role = Role::firstOrCreate(
                ['name' => 'Super Admin', 'guard_name' => 'web']
            );
        }

        // Assign role to user
        $user->syncRoles([$role]);

        echo "Super admin created: {$user->email} with role: {$role->name}\n";
    }
}
