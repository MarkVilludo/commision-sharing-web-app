<?php

use App\Http\Controllers\Admin\CommissionDistributionController;
use App\Http\Controllers\Admin\CommissionMonthReportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/commissions', [CommissionDistributionController::class, 'index'])->name('commissions.index');
    Route::post('/commissions', [CommissionDistributionController::class, 'store'])->name('commissions.store');

    Route::get('/commission-months', [CommissionMonthReportController::class, 'index'])->name('commission-months.index');
    Route::get('/commission-months/{report}', [CommissionMonthReportController::class, 'show'])->name('commission-months.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
