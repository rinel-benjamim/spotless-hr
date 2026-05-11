<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Justification;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Classe responsável por JustificationController.
 */
class JustificationController extends Controller
{
    /**
     * Lista justificativas com filtros.
     */
    public function index()
    {
        $query = Justification::with(['employee', 'attendance', 'justifiedBy']);

        if (! auth()->user()->canViewAllData()) {
            $query->where('employee_id', auth()->user()->employee->id);
        }

        $justifications = $query->latest()
            ->paginate(20)
            ->through(function ($j) {
                $j->attachment_path_encoded = $j->attachment_path
                    ? base64_encode($j->attachment_path)
                    : null;

                return $j;
            });

        return Inertia::render('Justifications/Index', [
            'justifications' => $justifications,
            'canApprove' => auth()->user()->isAdmin(),
        ]);
    }

    /**
     * Formulário para criar justificativa.
     */
    public function create(Request $request)
    {
        if (! auth()->user()->role->canCreateJustification()) {
            abort(403, 'Não tem permissão para criar justificativas.');
        }

        $user = auth()->user();
        $absenceDate = $request->query('absence_date');

        if ($user->isEmployee()) {
            $employeeId = $user->employee->id;
            $employees = Employee::where('id', $employeeId)
                ->select('id', 'full_name', 'employee_code')
                ->get();
            $selectedEmployee = $user->employee;
            $canSelectAllEmployees = false;
        } else {
            $employees = Employee::select('id', 'full_name', 'employee_code')
                ->orderBy('full_name')
                ->get();
            $selectedEmployee = $user->employee;
            $canSelectAllEmployees = true;
        }

        return Inertia::render('Justifications/Create', [
            'employees' => $employees,
            'selectedEmployee' => $selectedEmployee,
            'absenceDate' => $absenceDate,
            'canSelectAllEmployees' => $canSelectAllEmployees,
        ]);
    }

    /**
     * Cria nova justificativa (status: pending).
     */
    public function store(Request $request)
    {
        if (! auth()->user()->role->canCreateJustification()) {
            abort(403, 'Não tem permissão para criar justificativas.');
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_id' => ['nullable', 'exists:attendances,id'],
            'absence_date' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        if (auth()->user()->isEmployee() && $validated['employee_id'] != auth()->user()->employee->id) {
            abort(403, 'Apenas pode justificar as suas próprias faltas.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('justifications', 'local');
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

    /**
     * Aprova justificativa (admin).
     */
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

    /**
     * Rejeita justificativa (admin).
     */
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

    /**
     * Remove justificativa.
     */
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
