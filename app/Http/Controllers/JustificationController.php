<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Justification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JustificationController extends Controller
{
    // Lista justificativas com filtros
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

    // Formulário para criar justificativa
    public function create(Request $request)
    {
        // Apenas funcionários podem criar justificativas
        // Admin e Manager não podem criar justificativas para ninguém
        if (auth()->user()->isAdmin() || auth()->user()->isManager()) {
            abort(403, 'Apenas funcionários podem justificar faltas.');
        }

        $employeeId = auth()->user()->employee->id;
        $absenceDate = $request->query('absence_date');
        $employee = Employee::findOrFail($employeeId);

        $employees = Employee::where('id', $employeeId)
            ->select('id', 'full_name', 'employee_code')
            ->get();

        return Inertia::render('Justifications/Create', [
            'employees' => $employees,
            'selectedEmployee' => $employee,
            'absenceDate' => $absenceDate,
        ]);
    }

    // Cria nova justificativa (status: pending)
    public function store(Request $request)
    {
        // Apenas funcionários podem criar justificativas
        if (auth()->user()->isAdmin() || auth()->user()->isManager()) {
            abort(403, 'Apenas funcionários podem justificar faltas.');
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_id' => ['nullable', 'exists:attendances,id'],
            'absence_date' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        // Funcionário só pode justificar para si mesmo
        $employeeId = auth()->user()->employee->id;
        if ($validated['employee_id'] != $employeeId) {
            abort(403, 'Apenas pode justificar as suas próprias faltas.');
        }

        // Processar anexo (opcional)
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('justifications', 'public');
        }

        $validated['justified_by'] = auth()->id();
        $validated['status'] = 'pending';
        if ($attachmentPath) {
            $validated['attachment_path'] = $attachmentPath;
        }

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

    // Aprova justificativa (admin)
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

    // Rejeita justificativa (admin)
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

    // Remove justificativa
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
