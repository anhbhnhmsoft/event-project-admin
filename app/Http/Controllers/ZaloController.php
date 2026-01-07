<?php

namespace App\Http\Controllers;

class ZaloController extends Controller
{
    public function hook()
    {
        return response()->json( data: [['status' => 'success']], status: 200 );
    }
}
