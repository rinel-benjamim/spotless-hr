<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JustificationController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/export-kpis', [DashboardController::class, 'exportKpis'])->name('dashboard.export-kpis');

    Route::resource('employees', EmployeeController::class);
    Route::resource('shifts', ShiftController::class);

    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/export-pdf', [AttendanceController::class, 'exportPdf'])->name('attendances.export-pdf');
    Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::post('attendances/check-in', [AttendanceController::class, 'checkIn'])->name('attendances.check-in');
    Route::post('attendances/check-out', [AttendanceController::class, 'checkOut'])->name('attendances.check-out');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/employee/{employee}', [ReportController::class, 'employee'])->name('reports.employee');
    Route::get('reports/employee/{employee}/export-pdf', [ReportController::class, 'exportEmployeePdf'])->name('reports.employee-pdf');
    Route::get('reports/calendar/{employee}', [ReportController::class, 'calendar'])->name('reports.calendar');
    Route::get('reports/calendar/{employee}/export-pdf', [ReportController::class, 'exportCalendarPdf'])->name('reports.calendar-pdf');

    Route::get('justifications', [JustificationController::class, 'index'])->name('justifications.index');
    Route::get('justifications/create', [JustificationController::class, 'create'])->name('justifications.create');
    Route::post('justifications', [JustificationController::class, 'store'])->name('justifications.store');
    Route::delete('justifications/{justification}', [JustificationController::class, 'destroy'])->name('justifications.destroy');

    Route::get('absences', [\App\Http\Controllers\AbsenceController::class, 'index'])->name('absences.index');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::get('payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
    Route::get('payrolls/export-pdf', [PayrollController::class, 'exportListPdf'])->name('payrolls.export-list-pdf');
    Route::get('payrolls/create', [PayrollController::class, 'create'])->name('payrolls.create');
    Route::post('payrolls', [PayrollController::class, 'store'])->name('payrolls.store');
    Route::get('payrolls/{payroll}', [PayrollController::class, 'show'])->name('payrolls.show');
    Route::get('payrolls/{payroll}/export-pdf', [PayrollController::class, 'exportPdf'])->name('payrolls.export-pdf');
    Route::put('payrolls/{payroll}', [PayrollController::class, 'update'])->name('payrolls.update');
    Route::post('payrolls/{payroll}/mark-paid', [PayrollController::class, 'markAsPaid'])->name('payrolls.mark-paid');
    Route::post('payrolls/{payroll}/recalculate', [PayrollController::class, 'recalculate'])->name('payrolls.recalculate');

    Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('schedules/export-pdf', [ScheduleController::class, 'exportPdf'])->name('schedules.export-pdf');
    Route::get('schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::put('schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

    Route::get('settings/company', [CompanySettingsController::class, 'edit'])->name('company-settings.edit');
    Route::put('settings/company', [CompanySettingsController::class, 'update'])->name('company-settings.update');
});

require __DIR__.'/settings.php';
