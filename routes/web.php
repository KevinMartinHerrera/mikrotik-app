<?php

use App\Http\Controllers\api\RouterosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

