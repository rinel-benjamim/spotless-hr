<?php

namespace App\Http\Controllers;

use App\AttendanceType;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Classe responsável por AttendanceController.
 */
class AttendanceController extends Controller
{
    /**
     * Lista registros de ponto com filtros.
     */
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

    /**
     * Exporta registros de ponto para PDF.
     */
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

    /**
     * Aplica filtros na query de attendances.
     */
    private function getFilteredQuery(Request $request)
    {
        // Inicia query base com relacionamento de turno do funcionário.
        $query = Attendance::query()->with(['employee.shift']);

        // Restringe os resultados ao próprio usuário quando não há permissão para ver todos os dados.
        if (! auth()->user()->canViewAllData()) {
            // Usuários sem permissão veem apenas seus próprios registros.
            $query->where('employee_id', auth()->user()->employee->id);
        } elseif ($request->filled('employee_id')) {
            // Se tem permissão e foi especificado funcionário, filtra por ele.
            $query->where('employee_id', $request->employee_id);
        }

        // Aplica filtro de data inicial se fornecido.
        if ($request->filled('start_date')) {
            $query->whereDate('recorded_at', '>=', $request->start_date);
        }

        // Aplica filtro de data final se fornecido.
        if ($request->filled('end_date')) {
            $query->whereDate('recorded_at', '<=', $request->end_date);
        }

        // Retorna a query com todos os filtros aplicados.
        return $query;
    }

    /**
     * Registra ponto (check-in ou check-out automático).
     */
    public function store(StoreAttendanceRequest $request)
    {
        // Define o ID do funcionário (do request ou do usuário logado).
        $employeeId = $request->employee_id ?? auth()->user()->employee->id;

        // Verifica se o usuário tem permissão para marcar ponto para outro funcionário.
        if (! auth()->user()->canMarkAttendance() && $employeeId != auth()->user()->employee->id) {
            abort(403); // Aborta com erro 403 se não tiver permissão.
        }

        // Busca o último registro de ponto do funcionário para determinar o próximo tipo.
        $lastAttendance = Attendance::where('employee_id', $employeeId)
            ->latest('recorded_at') // Ordena pelo mais recente.
            ->first();

        // Lógica de alternância: se não há registro ou último foi check-out, faz check-in.
        // Caso contrário (último foi check-in), faz check-out.
        $type = (! $lastAttendance || $lastAttendance->type === AttendanceType::CheckOut)
            ? AttendanceType::CheckIn
            : AttendanceType::CheckOut;

        // Cria o novo registro de ponto com os dados fornecidos.
        Attendance::create([
            'employee_id' => $employeeId,
            'type' => $type, // Tipo determinado pela lógica acima.
            'recorded_at' => now(), // Timestamp atual.
            'notes' => $request->notes, // Notas opcionais do request.
        ]);

        // Redireciona de volta com mensagem de sucesso.
        return redirect()->back()
            ->with('success', 'Ponto registado com sucesso.');
    }

    /**
     * Registra entrada (check-in).
     */
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

    /**
     * Registra saída (check-out).
     */
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

    /**
     * Registra entrada para um funcionário (gerente/admin).
     */
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

    /**
     * Registra saída para um funcionário (gerente/admin).
     */
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
