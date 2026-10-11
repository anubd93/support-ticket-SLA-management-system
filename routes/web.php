<?php

use App\Http\Controllers\V1\AuthControllerV1;
use App\Http\Controllers\V1\DashboardControllerV1;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('login');
});

Route::get('login', [AuthControllerV1::class, 'getLogin'])->name('login');
Route::post('login', [AuthControllerV1::class, 'login']);
Route::get('logout', [AuthControllerV1::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardControllerV1::class, 'index'])->name('dashboard');
});
