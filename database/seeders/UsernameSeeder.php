<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsernameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate usernames for existing users who don't have one
        User::whereNull('username')->each(function ($user) {
            $baseUsername = Str::slug($user->name);
            $username = $baseUsername;
            $counter = 1;

            // Ensure uniqueness
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername.'-'.$counter;
                $counter++;
            }

            $user->update(['username' => $username]);
            $this->command->info("Generated username '{$username}' for user: {$user->name}");
        });

        $this->command->info('Username generation complete!');
    }
}
