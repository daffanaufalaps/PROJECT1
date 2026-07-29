<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:create
                            {--name=Admin : Admin name}
                            {--email= : Admin email}
                            {--password= : Admin password}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name');
        $email = $this->option('email') ?? $this->ask('Enter email address');
        $password = $this->option('password') ?? $this->secret('Enter password');

        if (!$email || !$password) {
            $this->error('Email and password are required.');
            return self::FAILURE;
        }

        // Check if email already exists
        if (Admin::where('email', $email)->exists()) {
            $this->error("Admin with email '{$email}' already exists.");
            return self::FAILURE;
        }

        $admin = Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Admin user created successfully!");
        $this->table(['ID', 'Name', 'Email'], [[
            $admin->id,
            $admin->name,
            $admin->email,
        ]]);

        return self::SUCCESS;
    }
}
