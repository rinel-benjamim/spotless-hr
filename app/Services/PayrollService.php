<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;

/**
 * Classe responsável por PayrollService.
 */
class PayrollService
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Gera folha de pagamento para um funcionário.
     */
    public function generatePayroll(Employee $employee, int $year, int $month): Payroll
    {
        // Cria uma data representando o mês de referência para a folha.
        $referenceMonth = Carbon::create($year, $month, 1);

        // Verifica se já existe uma folha de pagamento para este funcionário no mês.
        $existing = Payroll::where('employee_id', $employee->id)
            ->where('reference_month', $referenceMonth)
            ->first();

        // Se já existe, recalcula em vez de criar nova.
        if ($existing) {
            return $this->recalculatePayroll($existing);
        }

        // Obtém o resumo mensal de attendances do funcionário.
        $summary = $this->attendanceService->getMonthlySummary($employee, $year, $month);
        // Carrega as configurações atuais da empresa para cálculos.
        $settings = \App\Models\CompanySetting::current();

        // Define o salário base do funcionário (padrão 0 se não definido).
        $baseSalary = $employee->base_salary ?? 0;
        // Define o valor de dedução por ausência do funcionário.
        $deductionPerAbsence = $employee->deduction_per_absence ?? 0;

        // Calcula dedução por atrasos: quantidade de atrasos multiplicada pelo valor configurado.
        $lateDeduction = $summary['late_count'] * ($settings->late_deduction_amount ?? 0);
        // Calcula dedução por saídas antecipadas: quantidade multiplicada pelo valor configurado.
        $earlyExitDeduction = $summary['early_exit_count'] * ($settings->early_exit_deduction_amount ?? 0);
        // Calcula dedução por ausências: quantidade de ausências multiplicada pelo valor por ausência.
        $absenceDeduction = $summary['absence_count'] * $deductionPerAbsence;

        // Soma todas as deduções calculadas.
        $totalDeductions = $absenceDeduction + $lateDeduction + $earlyExitDeduction;
        // Calcula o salário líquido subtraindo as deduções do salário base.
        $netSalary = $baseSalary - $totalDeductions;

        // Cria e retorna o registro da folha de pagamento com todos os dados calculados.
        return Payroll::create([
            'employee_id' => $employee->id,
            'reference_month' => $referenceMonth,
            'base_salary' => $baseSalary,
            'total_days_worked' => $summary['days_worked'],
            'absences_count' => $summary['absence_count'],
            'late_count' => $summary['late_count'],
            'early_exit_count' => $summary['early_exit_count'],
            'total_deductions' => $totalDeductions,
            'total_bonus' => 0, // Bônus inicia em zero.
            'net_salary' => $netSalary,
        ]);
    }

    /**
     * Recalcula folha de pagamento.
     */
    public function recalculatePayroll(Payroll $payroll): Payroll
    {
        // Obtém o funcionário associado à folha de pagamento.
        $employee = $payroll->employee;
        // Converte o mês de referência para um objeto Carbon para extrair ano e mês.
        $date = Carbon::parse($payroll->reference_month);

        // Recalcula o resumo mensal com dados atualizados de attendances.
        $summary = $this->attendanceService->getMonthlySummary(
            $employee,
            $date->year,
            $date->month
        );
        // Carrega as configurações atuais da empresa.
        $settings = \App\Models\CompanySetting::current();

        // Usa o salário base atual do funcionário ou mantém o existente.
        $baseSalary = $employee->base_salary ?? $payroll->base_salary;
        // Define o valor de dedução por ausência do funcionário.
        $deductionPerAbsence = $employee->deduction_per_absence ?? 0;

        // Recalcula dedução por atrasos com dados atualizados.
        $lateDeduction = $summary['late_count'] * ($settings->late_deduction_amount ?? 0);
        // Recalcula dedução por saídas antecipadas.
        $earlyExitDeduction = $summary['early_exit_count'] * ($settings->early_exit_deduction_amount ?? 0);
        // Recalcula dedução por ausências.
        $absenceDeduction = $summary['absence_count'] * $deductionPerAbsence;

        // Soma todas as deduções recalculadas.
        $totalDeductions = $absenceDeduction + $lateDeduction + $earlyExitDeduction;
        // Recalcula o salário líquido incluindo bônus existentes.
        $netSalary = $baseSalary - $totalDeductions + $payroll->total_bonus;

        // Atualiza o registro da folha com os novos cálculos.
        $payroll->update([
            'base_salary' => $baseSalary,
            'total_days_worked' => $summary['days_worked'],
            'absences_count' => $summary['absence_count'],
            'late_count' => $summary['late_count'],
            'early_exit_count' => $summary['early_exit_count'],
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
        ]);

        // Retorna o registro atualizado com todas as relações recarregadas.
        return $payroll->fresh();
    }

    /**
     * Marca folha como paga.
     */
    public function markAsPaid(Payroll $payroll): Payroll
    {
        $payroll->update(['paid_at' => now()]);

        return $payroll->fresh();
    }

    /**
     * Gera folhas para todos os funcionários ativos.
     */
    public function generateForAllEmployees(int $year, int $month): int
    {
        // Busca todos os funcionários ativos que possuem salário base definido.
        $employees = Employee::where('status', 'active')
            ->whereNotNull('base_salary')
            ->get();

        $count = 0; // Contador de folhas geradas com sucesso.
        // Itera sobre cada funcionário elegível para gerar folha.
        foreach ($employees as $employee) {
            try {
                // Tenta gerar a folha de pagamento para o funcionário.
                $this->generatePayroll($employee, $year, $month);
                // Incrementa contador se geração for bem-sucedida.
                $count++;
            } catch (\Exception $e) {
                // Registra erro no log se a geração falhar para um funcionário.
                \Log::error('Erro ao gerar folha para funcionário', [
                    'employee_id' => $employee->id,
                    'name' => $employee->full_name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Retorna o número total de folhas geradas com sucesso.
        return $count;
    }
}
