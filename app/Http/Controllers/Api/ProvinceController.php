<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProvinceService;

class ProvinceController extends Controller
{
    protected ProvinceService $provinceService;

    public function __construct(ProvinceService $provinceService)
    {
        $this->provinceService = $provinceService;
    }

    public function getProvinces()
    {
        $provinces = $this->provinceService->getProvinces();
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => $provinces,
        ]);
    }

    public function getDistricts($code)
    {
        $district = $this->provinceService->getDistrictsByCodeProvince($code);
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => $district
        ]);
    }

    public function getWards($code)
    {
        $wards = $this->provinceService->getWardsByCodeDistrict($code);
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => $wards,
        ]);
    }
}
