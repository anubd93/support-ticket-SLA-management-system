<?php

use App\Http\Controllers\V1\AuthControllerV1;
use App\Http\Controllers\V1\DashboardControllerV1;
use App\Http\Controllers\V1\TicketAssignmentControllerV1;
use App\Http\Controllers\V1\TicketControllerV1;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('login');
});

Route::get('login', [AuthControllerV1::class, 'getLogin'])->name('login');
Route::post('login', [AuthControllerV1::class, 'login']);
Route::get('logout', [AuthControllerV1::class, 'logout'])->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('dashboard', [DashboardControllerV1::class, 'index'])->name('dashboard');

    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketControllerV1::class, 'index'])->name('tickets.index');
        Route::get('/queue', [TicketControllerV1::class, 'queue'])->name('tickets.queue');
        Route::get('/unassigned', [TicketControllerV1::class, 'unassigned'])->name('tickets.unassigned');
        Route::get('/resolved', [TicketControllerV1::class, 'resolved'])->name('tickets.resolved');
        Route::get('/create', [TicketControllerV1::class, 'create'])->name('tickets.create');
        Route::post('/', [TicketControllerV1::class, 'store'])->name('tickets.store');
        Route::get('/{id}', [TicketControllerV1::class, 'show'])->whereNumber('id')->name('tickets.show');
        Route::get('/{id}/edit', [TicketControllerV1::class, 'edit'])->whereNumber('id')->name('tickets.edit');
        Route::put('/{id}', [TicketControllerV1::class, 'update'])->whereNumber('id')->name('tickets.update');
        Route::patch('/{id}/status', [TicketControllerV1::class, 'updateStatus'])->whereNumber('id')->name('tickets.status');
        Route::patch('/{id}/assign', [TicketAssignmentControllerV1::class, 'assign'])->whereNumber('id')->name('tickets.assign');
        Route::post('/{id}/claim', [TicketAssignmentControllerV1::class, 'claim'])->whereNumber('id')->name('tickets.claim');
    });
});
