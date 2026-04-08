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
        $query = Justification::with(['employee', 'attendance', 'justifiedBy']);

        if (! auth()->user()->canViewAllData()) {
            $query->where('employee_id', auth()->user()->employee->id);
        }

        $justifications = $query->latest()
            ->paginate(20);

        return Inertia::render('Justifications/Index', [
            'justifications' => $justifications,
            'canApprove' => auth()->user()->isAdmin(),
        ]);
    }

    public function create(Request $request)
    {
        $employeeId = $request->query('employee_id');

        if (! auth()->user()->canViewAllData()) {
            $employeeId = auth()->user()->employee->id;
        }

        $absenceDate = $request->query('absence_date');
        $employee = $employeeId ? Employee::findOrFail($employeeId) : null;

        $employees = auth()->user()->canViewAllData()
            ? Employee::select('id', 'full_name', 'employee_code')->orderBy('full_name')->get()
            : Employee::where('id', auth()->user()->employee->id)->select('id', 'full_name', 'employee_code')->get();

        return Inertia::render('Justifications/Create', [
            'employees' => $employees,
            'selectedEmployee' => $employee,
            'absenceDate' => $absenceDate,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_id' => ['nullable', 'exists:attendances,id'],
            'absence_date' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if (! auth()->user()->canViewAllData()) {
            if ($validated['employee_id'] != auth()->user()->employee->id) {
                abort(403);
            }
        }

        $validated['justified_by'] = auth()->id();
        $validated['status'] = 'pending';

        $justification = Justification::create($validated);

        ActivityLog::log(
            'justification_created',
            $justification,
            'Justificativa submetida para aprovação',
            ['employee_id' => $validated['employee_id']]
        );

        return redirect()->route('justifications.index')
            ->with('success', 'Justificativa submetida. Aguarde aprovação do administrador.');
    }

    public function approve(Justification $justification)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $justification->update([
            'status' => 'approved',
            'justified_by' => auth()->id(),
        ]);

        ActivityLog::log(
            'justification_approved',
            $justification,
            'Justificativa aprovada',
            ['employee_id' => $justification->employee_id]
        );

        return redirect()->back()
            ->with('success', 'Justificativa aprovada com sucesso.');
    }

    public function reject(Justification $justification)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $justification->update([
            'status' => 'rejected',
            'justified_by' => auth()->id(),
        ]);

        ActivityLog::log(
            'justification_rejected',
            $justification,
            'Justificativa rejeitada',
            ['employee_id' => $justification->employee_id]
        );

        return redirect()->back()
            ->with('success', 'Justificativa rejeitada.');
    }

    public function destroy(Justification $justification)
    {
        if (! auth()->user()->canManageEmployees()) {
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
