<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Models\Organizer;
use App\Models\User;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\ConfigType;
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

        $organization = Organizer::insert([
            'id'     => 1,
            'name'   => 'Michec',
            'status' => CommonStatus::ACTIVE->value
        ]);

        $user = User::insert([
            'name'         => 'Michec',
            'email'        => 'admin@admin.vn',
            'role'         => RoleUser::SUPER_ADMIN->value,
            'organizer_id' => 1,
            'password'     => Hash::make('Test12345678@'),
            'lang'         => 'vi',
        ]);

        $configs = [
            [
                'config_key' => ConfigName::CLIENT_ID_APP->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => '4fd8acf9-513d-4c49-85ae-4316e708ab5f',
                'description' => 'ID ứng dụng ở kênh thanh toán',
                'organizer_id' => 1
            ],
            [
                'config_key' => ConfigName::API_KEY->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => 'af86c50a-adc1-452a-af1a-00924bb3d1d5',
                'description' => 'Mã API ở kênh thanh toán',
                'organizer_id' => 1
            ],
            [
                'config_key' => ConfigName::CHECKSUM_KEY->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => 'eb035b04d7ad00367f362de8cdede95c73d68392bbc0c5cb0ac53441f0a18176',
                'description' => 'Mã CHECKSUM ở kênh thanh toán',
                'organizer_id' => 1
            ],
            [
                'config_key' => ConfigName::LINK_ZALO_SUPPORT->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => 'https://zalo.me/your-support-link',
                'description' => 'Link hỗ trợ Zalo của hệ thống',
                'organizer_id' => 1
            ],
            [
                'config_key' => ConfigName::LINK_FACEBOOK_SUPPORT->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => 'https://facebook.com/your-support-page',
                'description' => 'Link trang hỗ trợ Facebook của hệ thống',
                'organizer_id' => 1
            ],
        ];


        Config::insert($configs);
    }
}
