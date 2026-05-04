<?php

namespace App\Http\Controllers;

use App\AttendanceType;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    // Lista registros de ponto com filtros
    public function index(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $attendances = $query->latest('recorded_at')->paginate(20);

        $employees = auth()->user()->canViewAllData()
            ? Employee::select('id', 'full_name')->get()
            : Employee::where('id', auth()->user()->employee->id)->select('id', 'full_name')->get();

        return Inertia::render('Attendances/Index', [
            'attendances' => $attendances,
            'employees' => $employees,
            'filters' => $request->only(['employee_id', 'start_date', 'end_date']),
            'canViewAllData' => auth()->user()->canViewAllData(),
        ]);
    }

    // Exporta registros de ponto para PDF
    public function exportPdf(Request $request)
    {
        if (! auth()->user()->canViewAllData() && $request->employee_id != auth()->user()->employee->id) {
            $request->merge(['employee_id' => auth()->user()->employee->id]);
        }

        $attendances = $this->getFilteredQuery($request)->latest('recorded_at')->get();
        $employee = $request->filled('employee_id') ? Employee::find($request->employee_id) : null;

        $pdf = Pdf::loadView('pdf.attendances', compact('attendances', 'employee', 'request'));

        $filename = $employee
            ? 'Presencas_'.$employee->full_name.'_'.now()->format('Y-m').'.pdf'
            : 'Presencas_Geral_'.now()->format('Y-m').'.pdf';

        return $pdf->download($filename);
    }

    // Aplica filtros na query de attendances
    private function getFilteredQuery(Request $request)
    {
        $query = Attendance::query()->with(['employee.shift']);

        if (! auth()->user()->canViewAllData()) {
            $query->where('employee_id', auth()->user()->employee->id);
        } elseif ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('recorded_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('recorded_at', '<=', $request->end_date);
        }

        return $query;
    }

    // Registra ponto (check-in ou check-out automático)
    public function store(StoreAttendanceRequest $request)
    {
        $employeeId = $request->employee_id ?? auth()->user()->employee->id;

        if (! auth()->user()->canMarkAttendance() && $employeeId != auth()->user()->employee->id) {
            abort(403);
        }

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

    // Registra entrada (check-in)
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

    // Registra saída (check-out)
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

    // Registra entrada para um funcionário (gerente/admin)
    public function checkInEmployee(Request $request)
    {
        if (! auth()->user()->canMarkAttendance()) {
            abort(403);
        }

        $employeeId = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ])['employee_id'];

        Attendance::create([
            'employee_id' => $employeeId,
            'type' => AttendanceType::CheckIn,
            'recorded_at' => now(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()
            ->with('success', 'Entrada registada com sucesso.');
    }

    // Registra saída para um funcionário (gerente/admin)
    public function checkOutEmployee(Request $request)
    {
        if (! auth()->user()->canMarkAttendance()) {
            abort(403);
        }

        $employeeId = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ])['employee_id'];

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
