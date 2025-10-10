<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\ConfigType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class InitApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init-application';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Khơi tạo ứng dụng với các thiết lập ban đầu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $migrateCode = Artisan::call('migrate');
        $this->info('--- Khởi tạo database');


        if ($migrateCode === Command::SUCCESS) {
            $this->info('Lệnh migrate đã thành công!');
        } else {
            $this->error('Lỗi khi chạy migrate!');
            return Command::FAILURE;
        }

        $this->info('--- Seeding ---');
        $rProvince = $this->initProvinces();
        if ($rProvince === false) {
            $this->error('Lỗi khi chạy Seeding initProvinces');
            return Command::FAILURE;
        }
        $rConfig = $this->initSeedConfig();
        if ($rConfig === false) {
            $this->error('Lỗi khi chạy initSeedConfig');
            return Command::FAILURE;
        }
        $this->info('Seeding database thành công');
        return Command::SUCCESS;
    }

    private function initProvinces(): bool
    {
        DB::beginTransaction();
        try {
            $responseProvince = Http::get('https://provinces.open-api.vn/api/v1/p/');
            if ($responseProvince->successful()) {
                $data = $responseProvince->json();  // Lấy dữ liệu dưới dạng mảng
                // Lưu dữ liệu vào bảng provinces
                foreach ($data as $provinceData) {
                    Province::query()->updateOrCreate(
                        ['code' => $provinceData['code']],
                        [
                            'name' => $provinceData['name'],
                            'division_type' => $provinceData["division_type"],
                        ]
                    );
                }
            } else {
                DB::rollBack();
                return false;
            }

            $responseDistricts = Http::get('https://provinces.open-api.vn/api/v1/d/');
            if ($responseDistricts->successful()) {
                $data = $responseDistricts->json();  // Lấy dữ liệu dưới dạng mảng
                // Lưu dữ liệu vào bảng provinces
                foreach ($data as $district) {
                    District::query()->updateOrCreate(
                        ['code' => $district['code']],
                        [
                            'name' => $district['name'],
                            'division_type' => $district["division_type"],
                            'province_code' => $district['province_code']
                        ]
                    );
                }
            } else {
                DB::rollBack();
                return false;
            }

            $responseWards = Http::get('https://provinces.open-api.vn/api/v1/w/');
            if ($responseWards->successful()) {
                $data = $responseWards->json();  // Lấy dữ liệu dưới dạng mảng
                // Lưu dữ liệu vào bảng provinces
                foreach ($data as $ward) {
                    Ward::query()->updateOrCreate(
                        ['code' => $ward['code']],
                        [
                            'name' => $ward['name'],
                            'division_type' => $ward["division_type"],
                            'district_code' => $ward['district_code']
                        ]
                    );
                }
            } else {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    private function initSeedConfig()
    {
        DB::beginTransaction();
        try {
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
            Config::query()->insert($configs);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }

    }
}
