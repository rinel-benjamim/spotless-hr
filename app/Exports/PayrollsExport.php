<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        return Payroll::with('employee')
            ->whereYear('reference_month', $this->year)
            ->whereMonth('reference_month', $this->month)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código',
            'Funcionário',
            'Mês Referência',
            'Salário Base',
            'Dias Trabalhados',
            'Faltas',
            'Atrasos',
            'Bónus',
            'Descontos',
            'Líquido',
            'Pago em',
        ];
    }

    public function map($payroll): array
    {
        return [
            $payroll->employee->employee_code,
            $payroll->employee->full_name,
            $payroll->reference_month,
            $payroll->base_salary,
            $payroll->total_days_worked,
            $payroll->absences_count,
            $payroll->late_count,
            $payroll->total_bonus,
            $payroll->total_deductions,
            $payroll->net_salary,
            $payroll->paid_at ? $payroll->paid_at->format('d/m/Y') : 'Pendente',
        ];
    }
}
