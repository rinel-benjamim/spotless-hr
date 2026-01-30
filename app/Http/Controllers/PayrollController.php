<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePayrollRequest;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService
    ) {}

    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        
        // Validar valores de ano e mês
        $year = max(2020, min(2030, $year));
        $month = max(1, min(12, $month));

        $query = Payroll::with('employee')
            ->whereYear('reference_month', $year)
            ->whereMonth('reference_month', $month);

        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            $query->where('employee_id', auth()->user()->employee->id);
        }

        $payrolls = $query->latest('reference_month')
            ->paginate(20);

        return Inertia::render('Payrolls/Index', [
            'payrolls' => $payrolls,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function exportListPdf(Request $request)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $payrolls = Payroll::with('employee')
            ->whereYear('reference_month', $year)
            ->whereMonth('reference_month', $month)
            ->get();
            
        $pdf = Pdf::loadView('pdf.payrolls-list', compact('payrolls', 'year', 'month'))->setPaper('a4', 'landscape');
        
        return $pdf->download("folhas-pagamento-{$year}-{$month}.pdf");
    }

    public function create()
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $employees = Employee::where('status', 'active')
            ->whereNotNull('base_salary')
            ->select('id', 'full_name', 'employee_code', 'base_salary')
            ->get();

        return Inertia::render('Payrolls/Create', [
            'employees' => $employees,
        ]);
    }

    public function store(StorePayrollRequest $request)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $data = $request->validated();

        if (isset($data['generate_all'])) {
            $count = $this->payrollService->generateForAllEmployees(
                $data['year'],
                $data['month']
            );

            return redirect()->route('payrolls.index', [
                'year' => $data['year'],
                'month' => $data['month'],
            ])->with('success', "Geradas {$count} folhas de pagamento.");
        }

        $employee = Employee::findOrFail($data['employee_id']);
        $this->payrollService->generatePayroll(
            $employee,
            $data['year'],
            $data['month']
        );

        return redirect()->route('payrolls.index', [
            'year' => $data['year'],
            'month' => $data['month'],
        ])->with('success', 'Folha de pagamento gerada com sucesso.');
    }

    public function show(Payroll $payroll)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager() && $payroll->employee_id != auth()->user()->employee->id) {
            abort(403);
        }

        $payroll->load('employee.shift');

        return Inertia::render('Payrolls/Show', [
            'payroll' => $payroll,
        ]);
    }

    public function exportPdf(Payroll $payroll)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager() && $payroll->employee_id != auth()->user()->employee->id) {
            abort(403);
        }

        $payroll->load(['employee.shift']);
        
        $pdf = Pdf::loadView('pdf.payroll', compact('payroll'));
        
        return $pdf->download("folha-pagamento-{$payroll->employee->employee_code}-" . now()->format('Y-m-d') . ".pdf");
    }

    public function update(Request $request, Payroll $payroll)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $data = $request->validate([
            'total_bonus' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $payroll->update($data);
        $this->payrollService->recalculatePayroll($payroll);

        return redirect()->back()
            ->with('success', 'Folha atualizada com sucesso.');
    }

    public function markAsPaid(Payroll $payroll)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $this->payrollService->markAsPaid($payroll);

        return redirect()->back()
            ->with('success', 'Pagamento registrado com sucesso.');
    }

    public function recalculate(Payroll $payroll)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $this->payrollService->recalculatePayroll($payroll);

        return redirect()->back()
            ->with('success', 'Folha recalculada com sucesso.');
    }
}
