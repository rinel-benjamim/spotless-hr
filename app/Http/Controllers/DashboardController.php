<?php

namespace App\Http\Controllers;

use App\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->canViewAllData()) {
            return $this->adminDashboard();
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

        return Inertia::render('Dashboard/Admin', [
            'stats' => $stats,
            'recentAttendances' => $recentAttendances,
        ]);
    }

    public function exportKpis()
    {
        if (! auth()->user()->canViewAllData()) {
            abort(403);
        }

        $stats = $this->getAdminStats();
        $pdf = Pdf::loadView('pdf.dashboard-kpis', compact('stats'));
        return $pdf->download('dashboard-kpis-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getAdminStats()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();

        $presentToday = Attendance::whereDate('recorded_at', $today)
            ->where('type', AttendanceType::CheckIn)
            ->distinct('employee_id')
            ->count('employee_id');

        // Calculate total hours this month
        $events = Attendance::whereDate('recorded_at', '>=', $thisMonth)
            ->orderBy('employee_id')
            ->orderBy('recorded_at')
            ->get();

        $totalMinutes = 0;
        $currentCheckIn = null;
        $lastEmployeeId = null;

        foreach ($events as $event) {
            // Reset check-in if employee changes
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
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'presentToday' => $presentToday,
            'totalHoursThisMonth' => round($totalHoursThisMonth, 2),
            'monthName' => now()->translatedFormat('F Y'),
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
        ]);
    }
}
