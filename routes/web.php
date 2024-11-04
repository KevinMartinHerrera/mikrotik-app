<?php

use App\Http\Controllers\api\RouterosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('router.login');
});

Route::post('/routeros-connect', [RouterosController::class, 'routeros_connection'])->name('routeros.connect');
Route::get('/dashboard', function () {
    $routeros_data = session('routeros_data');
    return view('router.dashboard', compact('routeros_data'));
})->name('dashboard');

Route::post('/set-interface', [RouterosController::class, 'set_interface'])->name('set_interface');
Route::post('/add-new-address', [RouterosController::class, 'add_new_address'])->name('add_new_address');
Route::post('/add-ip-route', [RouterosController::class, 'add_ip_route'])->name('add_ip_route');

