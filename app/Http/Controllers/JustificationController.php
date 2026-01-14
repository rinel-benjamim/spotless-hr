<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Justification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JustificationController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $justifications = Justification::with(['employee', 'attendance', 'justifiedBy'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Justifications/Index', [
            'justifications' => $justifications,
        ]);
    }

    public function create(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $employeeId = $request->query('employee_id');
        $absenceDate = $request->query('absence_date');
        $employee = $employeeId ? Employee::findOrFail($employeeId) : null;

        $employees = Employee::select('id', 'full_name', 'employee_code')
            ->orderBy('full_name')
            ->get();

        return Inertia::render('Justifications/Create', [
            'employees' => $employees,
            'selectedEmployee' => $employee,
            'absenceDate' => $absenceDate,
        ]);
    }

    public function store(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_id' => ['nullable', 'exists:attendances,id'],
            'absence_date' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $validated['justified_by'] = auth()->id();

        $justification = Justification::create($validated);

        ActivityLog::log(
            'justification_created',
            $justification,
            'Justificativa criada para funcionário',
            ['employee_id' => $validated['employee_id']]
        );

        return redirect()->route('justifications.index')
            ->with('success', 'Justificativa criada com sucesso.');
    }

    public function destroy(Justification $justification)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $justification->delete();

        ActivityLog::log(
            'justification_deleted',
            null,
            'Justificativa removida',
            ['justification_id' => $justification->id]
        );

        return redirect()->back()
            ->with('success', 'Justificativa removida com sucesso.');
    }
}
