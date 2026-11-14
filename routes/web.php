<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceBookingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\TestDriveController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/test-drive', [TestDriveController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('test-drive.store');

Route::post('/service-booking', [ServiceBookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('service-booking.store');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('my-services', [ServiceController::class, 'myServices'])->name('services.my');
    Route::get('my-services/{service}', [ServiceController::class, 'track'])->name('services.track');
    Route::get('my-test-drives', [TestDriveController::class, 'myTestDrives'])->name('test-drives.my');
    Route::get('services/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('services/{service}/status-stream', [ServiceController::class, 'statusStream'])->name('services.status-stream');

    // Notification Center Routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    Route::middleware('role:sales,super_admin')->group(function () {
        Route::post('cars/reorder', [CarController::class, 'reorder'])->name('cars.reorder');
        Route::patch('cars/{slug}/restore', [CarController::class, 'restore'])->name('cars.restore');
        Route::resource('cars', CarController::class);
        Route::resource('promos', PromoController::class)->except(['show']);
        Route::patch('test-drives/{testDrive}/approve', [TestDriveController::class, 'approve'])->name('test-drives.approve');
        Route::patch('test-drives/{testDrive}/reject', [TestDriveController::class, 'reject'])->name('test-drives.reject');
    });

    Route::middleware('role:sales,super_admin,mekanik')->group(function () {
        Route::patch('spare-parts/{slug}/restore', [SparePartController::class, 'restore'])->name('spare-parts.restore');
        Route::resource('spare-parts', SparePartController::class);
    });

    Route::middleware('role:mekanik,super_admin')->group(function () {
        Route::patch('services/{service}/status', [ServiceController::class, 'updateStatus'])->name('services.update-status');
        Route::post('services/{service}/items', [ServiceController::class, 'addItem'])->name('services.add-item');
        Route::delete('services/{service}/items/{item}', [ServiceController::class, 'removeItem'])->name('services.remove-item');
    });

    Route::get('reports', [ReportController::class, 'index'])->middleware('role:super_admin,owner')->name('reports.index');
    Route::post('reports/submit-to-owner', [ReportController::class, 'submitToOwner'])->middleware('role:super_admin')->name('reports.submit-to-owner');

    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::patch('services/{service}/assign', [ServiceController::class, 'assignMechanic'])->name('services.assign');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
