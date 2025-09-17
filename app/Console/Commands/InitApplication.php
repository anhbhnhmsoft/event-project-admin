<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
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

        $this->info('--- Seeding tỉnh thành');
        $rProvince = $this->initProvinces();
        if ($rProvince === true) {
            $this->info('Seeding tỉnh thành thành công');
        }else{
            $this->error('Lỗi khi chạy Seeding tỉnh thành!');
            return Command::FAILURE;
        }
        $this->info('--- Seeding demo database');
        DB::beginTransaction();
        DB::commit();
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
}
