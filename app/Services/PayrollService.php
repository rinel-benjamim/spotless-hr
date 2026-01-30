<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;

class PayrollService
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function generatePayroll(Employee $employee, int $year, int $month): Payroll
    {
        $referenceMonth = Carbon::create($year, $month, 1);

        $existing = Payroll::where('employee_id', $employee->id)
            ->where('reference_month', $referenceMonth)
            ->first();

        if ($existing) {
            return $this->recalculatePayroll($existing);
        }

        $summary = $this->attendanceService->getMonthlySummary($employee, $year, $month);
        $settings = \App\Models\CompanySetting::current();

        $baseSalary = $employee->base_salary ?? 0;
        $deductionPerAbsence = $employee->deduction_per_absence ?? 0;

        $lateDeduction = $summary['late_count'] * ($settings->late_deduction_amount ?? 0);
        $earlyExitDeduction = $summary['early_exit_count'] * ($settings->early_exit_deduction_amount ?? 0);
        $absenceDeduction = $summary['absence_count'] * $deductionPerAbsence;

        $totalDeductions = $absenceDeduction + $lateDeduction + $earlyExitDeduction;
        $netSalary = $baseSalary - $totalDeductions;

        return Payroll::create([
            'employee_id' => $employee->id,
            'reference_month' => $referenceMonth,
            'base_salary' => $baseSalary,
            'total_days_worked' => $summary['days_worked'],
            'absences_count' => $summary['absence_count'],
            'late_count' => $summary['late_count'],
            'early_exit_count' => $summary['early_exit_count'],
            'total_deductions' => $totalDeductions,
            'total_bonus' => 0,
            'net_salary' => $netSalary,
        ]);
    }

    public function recalculatePayroll(Payroll $payroll): Payroll
    {
        $employee = $payroll->employee;
        $date = Carbon::parse($payroll->reference_month);

        $summary = $this->attendanceService->getMonthlySummary(
            $employee,
            $date->year,
            $date->month
        );
        $settings = \App\Models\CompanySetting::current();

        $baseSalary = $employee->base_salary ?? $payroll->base_salary;
        $deductionPerAbsence = $employee->deduction_per_absence ?? 0;

        $lateDeduction = $summary['late_count'] * ($settings->late_deduction_amount ?? 0);
        $earlyExitDeduction = $summary['early_exit_count'] * ($settings->early_exit_deduction_amount ?? 0);
        $absenceDeduction = $summary['absence_count'] * $deductionPerAbsence;

        $totalDeductions = $absenceDeduction + $lateDeduction + $earlyExitDeduction;
        $netSalary = $baseSalary - $totalDeductions + $payroll->total_bonus;

        $payroll->update([
            'base_salary' => $baseSalary,
            'total_days_worked' => $summary['days_worked'],
            'absences_count' => $summary['absence_count'],
            'late_count' => $summary['late_count'],
            'early_exit_count' => $summary['early_exit_count'],
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
        ]);

        return $payroll->fresh();
    }

    public function markAsPaid(Payroll $payroll): Payroll
    {
        $payroll->update(['paid_at' => now()]);

        return $payroll->fresh();
    }

    public function generateForAllEmployees(int $year, int $month): int
    {
        $employees = Employee::where('status', 'active')
            ->whereNotNull('base_salary')
            ->get();

        $count = 0;
        foreach ($employees as $employee) {
            try {
                $this->generatePayroll($employee, $year, $month);
                $count++;
            } catch (\Exception $e) {
                \Log::error('Erro ao gerar folha para funcionário', [
                    'employee_id' => $employee->id, 
                    'name' => $employee->full_name,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $count;
    }
}
