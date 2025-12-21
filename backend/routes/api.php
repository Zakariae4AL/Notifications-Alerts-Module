<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/notifications', [NotificationController::class, 'index']);

Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/trigger', [NotificationController::class, 'trigger']);
Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update']);


?>