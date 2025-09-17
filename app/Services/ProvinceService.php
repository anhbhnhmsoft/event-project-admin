<?php

namespace App\Services;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;

class ProvinceService
{

    public function getProvinces(): \Illuminate\Database\Eloquent\Collection
    {
        return Province::query()->select(['code', 'name'])->get();
    }

    public function getDistrictsByCodeProvince(string $codeProvince): \Illuminate\Database\Eloquent\Collection
    {
        return District::query()->where('province_code', $codeProvince)->select(['code', 'name'])->get();
    }

    public function getWardsByCodeDistrict(string $codeDistrict): \Illuminate\Database\Eloquent\Collection
    {
        return Ward::query()->where('district_code', $codeDistrict)->select(['code', 'name'])->get();
    }
}
