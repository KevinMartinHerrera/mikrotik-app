<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class RouterosController extends Controller
{
    public function test_api()
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Welcome to Routeros API'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error Fetching Data from Routeros API'
            ]);
        }
    }
}
