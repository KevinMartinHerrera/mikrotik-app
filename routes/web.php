<?php

use App\Http\Controllers\api\RouterosController;
use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

Route::get('/',[FrontendController::class,'showLogin'])->name('show.login');
Route::get('/dashboard', [FrontendController::class, 'showDashboard'])->name('show.Dashboard');
Route::get('/logout', [FrontendController::class, 'logout'])->name('logout');

Route::prefix('v1')->group(function(){
    Route::post('/routeros-connect', [RouterosController::class, 'routeros_connection'])->name('routeros.connect');
    Route::post('/add-address', [RouterosController::class, 'add_new_address'])->name('add.address');
    Route::post('/add-route', [RouterosController::class, 'add_ip_route'])->name('add.route');
    Route::post('/add-dns', [RouterosController::class, 'add_dns_servers'])->name('add.dns');
    Route::post('/masquerade-srcnat', [RouterosController::class, 'masquerade_srcnat'])->name('masquerade.srcnat');
    Route::post('/routeros-shutdown', [RouterosController::class, 'routeros_shutdown']);
    Route::post('/add-user', [RouterosController::class, 'addUser'])->name('add.user');
    Route::post('/routeros/set-bandwidth', [RouterosController::class, 'setBandwidth'])->name('set.bandwidth');
    Route::post('/create_user_group', [RouterosController::class, 'create_user_group'])->name('create.user.group');

    Route::post('/routeros-reboot', [RouterosController::class, 'routeros_reboot'])->name('routeros.reboot');

    Route::post('/routeros-shutdown', [RouterosController::class, 'routeros_shutdown'])->name('routeros.shutdown');

});

