<?php

namespace App\Http\Controllers;

use App\AttendanceType;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::query()->with(['employee.shift']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('recorded_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('recorded_at', '<=', $request->end_date);
        }

        $attendances = $query->latest('recorded_at')->paginate(20);

        return Inertia::render('Attendances/Index', [
            'attendances' => $attendances,
            'employees' => Employee::select('id', 'full_name')->get(),
            'filters' => $request->only(['employee_id', 'start_date', 'end_date']),
        ]);
    }

    public function store(StoreAttendanceRequest $request)
    {
        $employeeId = $request->employee_id ?? auth()->user()->employee->id;

        $lastAttendance = Attendance::where('employee_id', $employeeId)
            ->latest('recorded_at')
            ->first();

        $type = (! $lastAttendance || $lastAttendance->type === AttendanceType::CheckOut)
            ? AttendanceType::CheckIn
            : AttendanceType::CheckOut;

        Attendance::create([
            'employee_id' => $employeeId,
            'type' => $type,
            'recorded_at' => now(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()
            ->with('success', 'Ponto registado com sucesso.');
    }

    public function checkIn(Request $request)
    {
        $employeeId = auth()->user()->employee->id;

        Attendance::create([
            'employee_id' => $employeeId,
            'type' => AttendanceType::CheckIn,
            'recorded_at' => now(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()
            ->with('success', 'Entrada registada com sucesso.');
    }

    public function checkOut(Request $request)
    {
        $employeeId = auth()->user()->employee->id;

        Attendance::create([
            'employee_id' => $employeeId,
            'type' => AttendanceType::CheckOut,
            'recorded_at' => now(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()
            ->with('success', 'Saída registada com sucesso.');
    }
}
