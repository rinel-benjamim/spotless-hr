<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePayrollRequest;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollService;
use App\Exports\PayrollsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService
    ) {}

    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $payrolls = Payroll::with('employee')
            ->whereYear('reference_month', $year)
            ->whereMonth('reference_month', $month)
            ->latest('reference_month')
            ->paginate(20);

        return Inertia::render('Payrolls/Index', [
            'payrolls' => $payrolls,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function exportListPdf(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $payrolls = Payroll::with('employee')
            ->whereYear('reference_month', $year)
            ->whereMonth('reference_month', $month)
            ->get();
            
        $pdf = Pdf::loadView('pdf.payrolls-list', compact('payrolls', 'year', 'month'))->setPaper('a4', 'landscape');
        
        return $pdf->download("folhas-pagamento-{$year}-{$month}.pdf");
    }

    public function exportListExcel(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        return Excel::download(new PayrollsExport($year, $month), "folhas-pagamento-{$year}-{$month}.xlsx");
    }

    public function create()
    {
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
        $payroll->load('employee.shift');

        return Inertia::render('Payrolls/Show', [
            'payroll' => $payroll,
        ]);
    }

    public function exportPdf(Payroll $payroll)
    {
        $payroll->load(['employee.shift']);
        
        $pdf = Pdf::loadView('pdf.payroll', compact('payroll'));
        
        return $pdf->download("folha-pagamento-{$payroll->employee->employee_code}-" . now()->format('Y-m-d') . ".pdf");
    }

    public function update(Request $request, Payroll $payroll)
    {
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
        $this->payrollService->markAsPaid($payroll);

        return redirect()->back()
            ->with('success', 'Pagamento registrado com sucesso.');
    }

    public function recalculate(Payroll $payroll)
    {
        $this->payrollService->recalculatePayroll($payroll);

        return redirect()->back()
            ->with('success', 'Folha recalculada com sucesso.');
    }
}
