<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAdminSeeder extends Seeder
{
    public const DEMO_EMAIL = 'admin@example.com';
    public const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        $restaurant = Restaurant::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Demo Restaurant',
                'slug' => 'demo-restaurant',
                'is_active' => true,
            ]
        );
        $restaurant->update(['slug' => 'demo-restaurant']);

        User::updateOrCreate(
            ['email' => self::DEMO_EMAIL],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make(self::DEMO_PASSWORD),
                'restaurant_id' => $restaurant->id,
                'role' => 'owner',
            ]
        );

        $this->command->info('Demo admin credentials:');
        $this->command->info('  Email: ' . self::DEMO_EMAIL);
        $this->command->info('  Password: ' . self::DEMO_PASSWORD);
        $this->command->newLine();
        $this->command->info('Login at: POST /api/admin/login');
    }
}
