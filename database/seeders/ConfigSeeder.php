<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\ConfigType;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $now = now();
        $configs = [
            // [
            //     'config_key' => ConfigName::CLIENT_ID_APP->value,
            //     'config_type' => ConfigType::STRING->value,
            //     'config_value' => '4fd8acf9-513d-4c49-85ae-4316e708ab5f',
            //     'description' => 'ID ứng dụng ở kênh thanh toán',
            //     'organizer_id' => 1,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'config_key' => ConfigName::API_KEY->value,
            //     'config_type' => ConfigType::STRING->value,
            //     'config_value' => 'af86c50a-adc1-452a-af1a-00924bb3d1d5',
            //     'description' => 'Mã API ở kênh thanh toán',
            //     'organizer_id' => 1,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'config_key' => ConfigName::CHECKSUM_KEY->value,
            //     'config_type' => ConfigType::STRING->value,
            //     'config_value' => 'eb035b04d7ad00367f362de8cdede95c73d68392bbc0c5cb0ac53441f0a18176',
            //     'description' => 'Mã CHECKSUM ở kênh thanh toán',
            //     'organizer_id' => 1,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'config_key' => ConfigName::LINK_ZALO_SUPPORT->value,
            //     'config_type' => ConfigType::STRING->value,
            //     'config_value' => 'https://zalo.me/your-support-link',
            //     'description' => 'Link hỗ trợ Zalo của hệ thống',
            //     'organizer_id' => 1,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'config_key' => ConfigName::LINK_FACEBOOK_SUPPORT->value,
            //     'config_type' => ConfigType::STRING->value,
            //     'config_value' => 'https://facebook.com/your-support-page',
            //     'description' => 'Link trang hỗ trợ Facebook của hệ thống',
            //     'organizer_id' => 1,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // Zalo Configs
            [
                'config_key' => ConfigName::ZALO_APP_ID->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => env('ZALO_APP_ID', ''),
                'description' => 'Zalo App ID',
                'organizer_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'config_key' => ConfigName::ZALO_APP_SECRET->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => env('ZALO_APP_SECRET', ''),
                'description' => 'Zalo App Secret',
                'organizer_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'config_key' => ConfigName::ZALO_OA_ID->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => env('ZALO_OA_ID', ''),
                'description' => 'Zalo OA ID',
                'organizer_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'config_key' => ConfigName::ZALO_REDIRECT_URI->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => env('ZALO_REDIRECT_URI', ''),
                'description' => 'Zalo Redirect URI',
                'organizer_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'config_key' => ConfigName::ZALO_OTP_TEMPLATES->value,
                'config_type' => ConfigType::STRING->value, // JSON stored as string
                'config_value' => json_encode([
                    'otp' => env('ZALO_OTP_TEMPLATE', ''),
                    'register' => env('ZALO_OTP_TEMPLATE_REGISTER', env('ZALO_OTP_TEMPLATE', '')),
                    'forgot_password' => env('ZALO_OTP_TEMPLATE_FORGOT_PASSWORD', env('ZALO_OTP_TEMPLATE', '')),
                    'verify_phone' => env('ZALO_OTP_TEMPLATE_VERIFY_PHONE', env('ZALO_OTP_TEMPLATE', '')),
                ]),
                'description' => 'Zalo OTP Templates (JSON)',
                'organizer_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];


        Config::insert($configs);
    }
}
