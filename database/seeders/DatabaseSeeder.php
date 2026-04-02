<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed admin user (only if no user exists)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        // Seed default site settings
        $defaults = [
            'hero_name'         => 'Nugraha Aryaguna',
            'hero_professions'  => ['Full Stack Developer', 'Laravel Specialist', 'Vue.js Enthusiast'],
            'hero_description'  => 'Passionate developer with expertise in building modern web applications.',
            'cv_file_path'      => null,
            'typing_speed'      => '100',
            'og_default_image'  => null,
            'admin_email'       => 'admin@example.com',
            'social_linkedin'   => '',
            'social_github'     => '',
            'social_email'      => 'admin@example.com',
        ];

        foreach ($defaults as $key => $value) {
            $encoded = (is_array($value) || is_object($value))
                ? json_encode($value)
                : $value;

            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $encoded]
            );
        }
    }
}
