<?php

namespace App\Http\Controllers;

use App\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->employeeDashboard();
    }

    protected function adminDashboard()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();

        $presentToday = Attendance::whereDate('recorded_at', $today)
            ->where('type', AttendanceType::CheckIn)
            ->distinct('employee_id')
            ->count('employee_id');

        $totalHoursThisMonth = DB::table('attendances as check_in')
            ->join('attendances as check_out', function ($join) {
                $join->on('check_in.employee_id', '=', 'check_out.employee_id')
                    ->whereRaw('DATE(check_in.recorded_at) = DATE(check_out.recorded_at)')
                    ->whereRaw('check_out.recorded_at > check_in.recorded_at');
            })
            ->where('check_in.type', AttendanceType::CheckIn->value)
            ->where('check_out.type', AttendanceType::CheckOut->value)
            ->whereDate('check_in.recorded_at', '>=', $thisMonth)
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, check_in.recorded_at, check_out.recorded_at) / 60'));

        $recentAttendances = Attendance::with('employee')
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard/Admin', [
            'stats' => [
                'totalEmployees' => $totalEmployees,
                'activeEmployees' => $activeEmployees,
                'presentToday' => $presentToday,
                'totalHoursThisMonth' => round($totalHoursThisMonth, 2),
            ],
            'recentAttendances' => $recentAttendances,
        ]);
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
