<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\ConfigType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $configs = [
            [
                'config_key' => ConfigName::CLIENT_ID_APP->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => '4fd8acf9-513d-4c49-85ae-4316e708ab5f',
                'description' => 'ID ứng dụng ở kênh thanh toán',
            ],
            [
                'config_key' => ConfigName::API_KEY->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => 'af86c50a-adc1-452a-af1a-00924bb3d1d5',
                'description' => 'Mã API ở kênh thanh toán',
            ],
            [
                'config_key' => ConfigName::CHECKSUM_KEY->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => 'eb035b04d7ad00367f362de8cdede95c73d68392bbc0c5cb0ac53441f0a18176',
                'description' => 'Mã CHECKSUM ở kênh thanh toán',
            ],
            [
                'config_key' => ConfigName::LINK_ZALO_SUPPORT->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => 'https://zalo.me/your-support-link',
                'description' => 'Link hỗ trợ Zalo của hệ thống',
            ],
            [
                'config_key' => ConfigName::LINK_FACEBOOK_SUPPORT->value,
                'config_type' => ConfigType::STRING->value,
                'config_value' => 'https://facebook.com/your-support-page',
                'description' => 'Link trang hỗ trợ Facebook của hệ thống',
            ],
        ];


        Config::insert($configs);
    }
}
