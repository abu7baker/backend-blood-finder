<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;

class HospitalController extends Controller
{
    public function index()
    {
        $hospitals = Hospital::where('status', 'verified')
    ->select('id', 'name')
    ->get();

        return response()->json([
            'success' => true,
            'hospitals' => $hospitals
        ], 200);
    }
}
