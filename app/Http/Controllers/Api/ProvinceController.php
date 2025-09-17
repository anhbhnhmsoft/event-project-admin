<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProvinceService;
use App\Utils\Constants\CommonStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'provinces' => $provinces,
        ]);
    }

    public function getDistricts($code)
    {
        $district = $this->provinceService->getDistrictsByCodeProvince($code);
        return response()->json([
            'districts' => $district
        ]);
    }

    public function getWards($code)
    {
        $wards = $this->provinceService->getWardsByCodeDistrict($code);
        return response()->json([
            'wards' => $wards,
        ]);
    }
}
