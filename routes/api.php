<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Customers
    Route::apiResource('customers', CustomerController::class)->names('api.customers');

    // Tickets
    Route::apiResource('tickets', TicketController::class)->names('api.tickets');

    // Technician Module
    Route::get('/technician/dashboard', [TechnicianController::class, 'dashboard']);
    Route::get('/technician/history', [TechnicianController::class, 'history']);

    // Module Manager
    Route::prefix('modules')->group(function () {
        Route::get('/', [\App\Http\Controllers\ModuleManagerController::class, 'index'])->name('api.modules.index');
        Route::get('/{alias}', [\App\Http\Controllers\ModuleManagerController::class, 'show'])->name('api.modules.show');
        Route::post('/{alias}/enable', [\App\Http\Controllers\ModuleManagerController::class, 'enable'])->name('api.modules.enable');
        Route::post('/{alias}/disable', [\App\Http\Controllers\ModuleManagerController::class, 'disable'])->name('api.modules.disable');
    });
});

// WhatsApp Webhook
Route::prefix('whatsapp')->group(function () {
    Route::match(['GET', 'POST'], '/webhook', [\App\Http\Controllers\WhatsAppWebhookController::class, 'handle'])
        ->middleware('throttle:120,1')
        ->name('api.whatsapp.webhook');
});

// dsBilling Attendance Sync Webhook
Route::post('/v1/attendance/sync', [\App\Http\Controllers\Api\AttendanceSyncApiController::class, 'sync'])
    ->middleware('throttle:120,1')
    ->name('api.attendance.sync');
Route::post('/attendance/sync', [\App\Http\Controllers\Api\AttendanceSyncApiController::class, 'sync']);

