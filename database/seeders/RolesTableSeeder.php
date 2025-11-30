<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Webtechsolutions\UserManager\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Guests',
                'slug' => 'guests',
                'description' => 'Guest users with limited access to the system',
                'is_supervisor' => false,
            ],
            [
                'name' => 'Members',
                'slug' => 'members',
                'description' => 'Regular members with standard access privileges',
                'is_supervisor' => false,
            ],
            [
                'name' => 'Creators',
                'slug' => 'creators',
                'description' => 'Content creators with extended permissions',
                'is_supervisor' => false,
            ],
            [
                'name' => 'Administrators',
                'slug' => 'administrators',
                'description' => 'System administrators with full access to all features',
                'is_supervisor' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}
