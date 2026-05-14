<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Create or promote a user to admin';

    public function handle()
    {
        $email = $this->ask('Enter admin email');
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->info("User found. Promoting {$email} to admin.");
            $user->role = 'admin';
            $user->save();
        } else {
            $this->info("User not found. Creating a new admin user.");
            $name = $this->ask('Enter name');
            $password = $this->secret('Enter password');
            
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
            ]);
        }

        $this->info('Admin user ready.');
    }
}
