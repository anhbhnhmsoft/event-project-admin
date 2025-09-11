<?php

namespace Database\Seeders;

use App\Models\User;
use App\Utils\Constants\Language;
use App\Utils\Constants\RoleUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tạo organizer
        $organizerId = DB::table('organizers')->insertGetId([
            'name' => 'Kamnex Organizer',
            'description' => 'Nhà tổ chức sự kiện Kamnex',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Tạo user admin với organizer_id
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('Test12345678@'),
            'role' => RoleUser::SUPER_ADMIN->value,
            'phone' => '0123456789',
            'address' => 'Hà Nội, Việt Nam',
            'organizer_id' => $organizerId,
            'lang' => Language::VI->value,
            'email_verified_at' => now()
        ]);

    }
}
