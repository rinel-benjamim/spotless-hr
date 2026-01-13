<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JustificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('employees', EmployeeController::class);
    Route::resource('shifts', ShiftController::class);

    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::post('attendances/check-in', [AttendanceController::class, 'checkIn'])->name('attendances.check-in');
    Route::post('attendances/check-out', [AttendanceController::class, 'checkOut'])->name('attendances.check-out');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/employee/{employee}', [ReportController::class, 'employee'])->name('reports.employee');
    Route::get('reports/calendar/{employee}', [ReportController::class, 'calendar'])->name('reports.calendar');

    Route::get('justifications', [JustificationController::class, 'index'])->name('justifications.index');
    Route::get('justifications/create', [JustificationController::class, 'create'])->name('justifications.create');
    Route::post('justifications', [JustificationController::class, 'store'])->name('justifications.store');
    Route::delete('justifications/{justification}', [JustificationController::class, 'destroy'])->name('justifications.destroy');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::get('settings/company', [CompanySettingsController::class, 'edit'])->name('company-settings.edit');
    Route::put('settings/company', [CompanySettingsController::class, 'update'])->name('company-settings.update');
});

require __DIR__.'/settings.php';
