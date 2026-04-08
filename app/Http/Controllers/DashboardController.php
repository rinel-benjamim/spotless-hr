<?php

namespace App\Http\Controllers;

use App\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isManager()) {
            return $this->managerDashboard();
        }

        return $this->employeeDashboard();
    }

    protected function adminDashboard()
    {
        $stats = $this->getAdminStats();

        $recentAttendances = Attendance::with('employee')
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        $pendingJustificationsCount = Justification::where('status', 'pending')->count();

        return Inertia::render('Dashboard/Admin', [
            'stats' => $stats,
            'recentAttendances' => $recentAttendances,
            'dashboardTitle' => 'Dashboard do Diretor',
            'pendingJustificationsCount' => $pendingJustificationsCount,
        ]);
    }

    protected function managerDashboard()
    {
        $stats = $this->getManagerStats();

        $employees = Employee::where('status', 'active')
            ->with('shift')
            ->get();

        $recentAttendances = Attendance::with('employee')
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard/Manager', [
            'stats' => $stats,
            'employees' => $employees,
            'recentAttendances' => $recentAttendances,
            'dashboardTitle' => 'Dashboard do Gerente',
        ]);
    }

    private function getManagerStats()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $activeEmployees = Employee::where('status', 'active')->count();

        $presentToday = Attendance::whereDate('recorded_at', $today)
            ->where('type', AttendanceType::CheckIn)
            ->distinct('employee_id')
            ->count('employee_id');

        $events = Attendance::whereDate('recorded_at', '>=', $thisMonth)
            ->orderBy('employee_id')
            ->orderBy('recorded_at')
            ->get();

        $totalMinutes = 0;
        $currentCheckIn = null;
        $lastEmployeeId = null;

        foreach ($events as $event) {
            if ($lastEmployeeId !== $event->employee_id) {
                $currentCheckIn = null;
                $lastEmployeeId = $event->employee_id;
            }

            if ($event->type === AttendanceType::CheckIn) {
                $currentCheckIn = $event->recorded_at;
            } elseif ($event->type === AttendanceType::CheckOut && $currentCheckIn) {
                $totalMinutes += $currentCheckIn->diffInMinutes($event->recorded_at);
                $currentCheckIn = null;
            }
        }

        $totalHoursThisMonth = $totalMinutes / 60;

        return [
            'activeEmployees' => $activeEmployees,
            'presentToday' => $presentToday,
            'totalHoursThisMonth' => round($totalHoursThisMonth, 2),
            'monthName' => now()->translatedFormat('F Y'),
        ];
    }

    public function exportKpis()
    {
        if (! auth()->user()->canViewAllData()) {
            abort(403);
        }

        $stats = $this->getAdminStats();
        $pdf = Pdf::loadView('pdf.dashboard-kpis', compact('stats'));

        return $pdf->download('dashboard-kpis-'.now()->format('Y-m-d').'.pdf');
    }

    private function getAdminStats()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();

        $presentToday = Attendance::whereDate('recorded_at', $today)
            ->where('type', AttendanceType::CheckIn)
            ->distinct('employee_id')
            ->count('employee_id');

        $events = Attendance::whereDate('recorded_at', '>=', $thisMonth)
            ->orderBy('employee_id')
            ->orderBy('recorded_at')
            ->get();

        $totalMinutes = 0;
        $currentCheckIn = null;
        $lastEmployeeId = null;

        foreach ($events as $event) {
            if ($lastEmployeeId !== $event->employee_id) {
                $currentCheckIn = null;
                $lastEmployeeId = $event->employee_id;
            }

            if ($event->type === AttendanceType::CheckIn) {
                $currentCheckIn = $event->recorded_at;
            } elseif ($event->type === AttendanceType::CheckOut && $currentCheckIn) {
                $totalMinutes += $currentCheckIn->diffInMinutes($event->recorded_at);
                $currentCheckIn = null;
            }
        }

        $totalHoursThisMonth = $totalMinutes / 60;

        $daysInMonth = now()->daysInMonth;
        $currentDay = now()->day;
        $averageDailyAttendance = $currentDay > 0
            ? round($events->where('type', AttendanceType::CheckIn)->groupBy(fn ($e) => $e->recorded_at->format('Y-m-d'))->count() / $currentDay, 1)
            : 0;

        $attendanceService = new \App\Services\AttendanceService;
        $allEmployees = Employee::where('status', 'active')->get();
        $totalAbsences = 0;
        $totalLates = 0;

        foreach ($allEmployees as $employee) {
            $summary = $attendanceService->getMonthlySummary($employee, now()->year, now()->month);
            $totalAbsences += $summary['absence_count'];
            $totalLates += $summary['late_count'];
        }

        $averageHoursPerEmployee = $activeEmployees > 0
            ? round($totalHoursThisMonth / $activeEmployees, 1)
            : 0;

        return [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'presentToday' => $presentToday,
            'totalHoursThisMonth' => round($totalHoursThisMonth, 2),
            'monthName' => now()->translatedFormat('F Y'),
            'averageDailyAttendance' => $averageDailyAttendance,
            'averageHoursPerEmployee' => $averageHoursPerEmployee,
            'totalAbsencesThisMonth' => $totalAbsences,
            'totalLatesThisMonth' => $totalLates,
        ];
    }

    protected function employeeDashboard()
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return Inertia::render('Dashboard/Employee', [
                'message' => 'Perfil de funcionário não encontrado.',
            ]);
        }

        $thisMonth = now()->startOfMonth();

        $lastAttendance = Attendance::where('employee_id', $employee->id)
            ->latest('recorded_at')
            ->first();

        $todayAttendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', now())
            ->get();

        $monthAttendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', '>=', $thisMonth)
            ->orderBy('recorded_at', 'desc')
            ->get();

        return Inertia::render('Dashboard/Employee', [
            'employee' => $employee->load('shift'),
            'lastAttendance' => $lastAttendance,
            'todayAttendances' => $todayAttendances,
            'monthAttendances' => $monthAttendances,
            'dashboardTitle' => 'Dashboard do Funcionário',
        ]);
    }
}
