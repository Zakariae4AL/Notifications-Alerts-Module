<?php

use App\Http\Controllers\Api\V1\AlertController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::patch('me', [AuthController::class, 'update'])->name('me.update');

        Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('unread-count', 'unreadCount')->name('unread-count');
            Route::patch('read-all', 'markAllAsRead')->name('read-all');
            Route::patch('{id}/read', 'markAsRead')->whereUuid('id')->name('read');
            Route::patch('{id}/unread', 'markAsUnread')->whereUuid('id')->name('unread');
            Route::delete('{id}', 'destroy')->whereUuid('id')->name('destroy');
        });

        Route::get('notification-preferences', [NotificationPreferenceController::class, 'index'])->name('preferences.index');
        Route::put('notification-preferences', [NotificationPreferenceController::class, 'update'])->name('preferences.update');

        Route::get('alerts/types', [AlertController::class, 'types'])->name('alerts.types');
        Route::post('alerts', [AlertController::class, 'store'])->name('alerts.store');
    });
});
