<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        Admin::create([
            'name' => 'Administrator',
            'email' => 'admin@gempa-webgis.test',
            'password' => Hash::make('admin123'),
        ]);

        $this->command->info('Default admin created: admin@gempa-webgis.test / admin123');
        $this->command->warn('Please change the default password immediately!');
    }
}
