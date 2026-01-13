<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::with('shift')->get();

        return Inertia::render('Reports/Index', [
            'employees' => $employees,
        ]);
    }

    public function employee(Request $request, Employee $employee)
    {
        $this->authorize('view', $employee);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = $employee->attendances()
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at', 'desc')
            ->get();

        $summary = $this->attendanceService->getMonthlySummary($employee, $year, $month);

        return Inertia::render('Reports/Employee', [
            'employee' => $employee->load('shift'),
            'attendances' => $attendances,
            'summary' => $summary,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function calendar(Request $request, Employee $employee)
    {
        $this->authorize('view', $employee);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = $employee->attendances()
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($a) => $a->recorded_at->format('Y-m-d'));

        $absences = $this->attendanceService->getAbsences($employee, $startDate, $endDate);

        return Inertia::render('Reports/Calendar', [
            'employee' => $employee->load('shift'),
            'attendances' => $attendances,
            'absences' => $absences,
            'year' => $year,
            'month' => $month,
        ]);
    }
}
