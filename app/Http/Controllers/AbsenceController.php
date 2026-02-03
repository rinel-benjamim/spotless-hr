<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbsenceController extends Controller
{
    public function index(Request $request, AttendanceService $attendanceService)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now()->endOfMonth();

        $employees = auth()->user()->canViewAllData() 
            ? Employee::all() 
            : collect([auth()->user()->employee]);
            
        $allAbsences = collect();

        foreach ($employees as $employee) {
            if (! $employee) continue;
            $absences = $attendanceService->getAbsences($employee, $startDate, $endDate);
            foreach ($absences as $absence) {
                $allAbsences->push([
                    'employee' => [
                        'id' => $employee->id,
                        'full_name' => $employee->full_name,
                        'employee_code' => $employee->employee_code,
                    ],
                    'date' => $absence['date']->format('Y-m-d'),
                    'type' => $absence['type'],
                ]);
            }
        }

        // Sort by date desc
        $allAbsences = $allAbsences->sortByDesc('date')->values();

        return Inertia::render('Absences/Index', [
            'absences' => $allAbsences,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'employees' => auth()->user()->canViewAllData() 
                ? Employee::select('id', 'full_name')->get()
                : Employee::where('id', auth()->user()->employee->id)->select('id', 'full_name')->get(),
        ]);
    }
}
