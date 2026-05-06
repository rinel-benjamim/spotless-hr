<?php

namespace App\Services;

use App\AbsenceType;
use App\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Classe responsável por AttendanceService.
 */
class AttendanceService
{
    /**
     * Registra ponto (entrada/saída).
     */
    public function recordAttendance(Employee $employee, AttendanceType $type, ?string $notes = null): Attendance
    {
        return Attendance::create([
            'employee_id' => $employee->id,
            'type' => $type,
            'recorded_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Verifica se pode fazer check-in.
     */
    public function canCheckIn(Employee $employee, ?Carbon $date = null): bool
    {
        $date = $date ?? now();

        $lastCheckIn = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', $date->toDateString())
            ->where('type', AttendanceType::CheckIn)
            ->exists();

        return ! $lastCheckIn;
    }

    /**
     * Verifica se pode fazer check-out.
     */
    public function canCheckOut(Employee $employee, ?Carbon $date = null): bool
    {
        $date = $date ?? now();

        $lastCheckIn = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', $date->toDateString())
            ->where('type', AttendanceType::CheckIn)
            ->exists();

        $lastCheckOut = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', $date->toDateString())
            ->where('type', AttendanceType::CheckOut)
            ->exists();

        return $lastCheckIn && ! $lastCheckOut;
    }

    /**
     * Calcula horas trabalhadas em um período.
     */
    public function calculateWorkedHours(Employee $employee, Carbon $startDate, Carbon $endDate): float
    {
        // Busca todos os registros de ponto do funcionário no intervalo informado, ordenados por horário.
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at')
            ->get();

        $totalMinutes = 0; // Acumulador para o total de minutos trabalhados.
        $checkIn = null; // Armazena o último check-in válido para emparelhar com check-out.

        // Percorre os eventos ordenados para emparelhar cada entrada com sua saída.
        foreach ($attendances as $attendance) {
            if ($attendance->type === AttendanceType::CheckIn) {
                // Quando encontra um check-in, armazena o horário para calcular o intervalo posterior.
                $checkIn = $attendance->recorded_at;
            } elseif ($attendance->type === AttendanceType::CheckOut && $checkIn) {
                // Quando encontra um check-out e há um check-in anterior, calcula o tempo decorrido.
                $totalMinutes += $checkIn->diffInMinutes($attendance->recorded_at);
                // Reseta o check-in após o cálculo para evitar reutilização em check-outs subsequentes.
                $checkIn = null;
            }
        }

        // Converte minutos para horas e retorna o valor total trabalhado.
        return $totalMinutes / 60;
    }

    /**
     * Calcula horas trabalhadas em um dia.
     */
    public function calculateDailyWorkedHours(Employee $employee, Carbon $date): float
    {
        // Coleta registros do funcionário apenas no dia informado, ordenados cronologicamente.
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', $date->toDateString())
            ->orderBy('recorded_at')
            ->get();

        $totalMinutes = 0; // Total de minutos trabalhados no dia.
        $checkIn = null; // Último check-in do dia para emparelhar com check-out.

        // Emparelha cada entrada com a próxima saída para montar as horas totais do dia.
        foreach ($attendances as $attendance) {
            if ($attendance->type === AttendanceType::CheckIn) {
                // Registra o horário de entrada para calcular o período até a saída.
                $checkIn = $attendance->recorded_at;
            } elseif ($attendance->type === AttendanceType::CheckOut && $checkIn) {
                // Calcula a diferença em minutos entre entrada e saída.
                $totalMinutes += $checkIn->diffInMinutes($attendance->recorded_at);
                // Limpa o check-in após o cálculo para preparar o próximo par.
                $checkIn = null;
            }
        }

        // Retorna as horas totais trabalhadas no dia.
        return $totalMinutes / 60;
    }

    /**
     * Verifica se é atraso.
     */
    public function isLate(Attendance $attendance): bool
    {
        // Apenas check-ins podem ser considerados atrasos.
        if ($attendance->type !== AttendanceType::CheckIn) {
            return false;
        }

        // Se o check-in tem justificativa aprovada, não é considerado atraso.
        if ($this->isJustified($attendance)) {
            return false;
        }

        // Obtém o funcionário e seu turno associado.
        $employee = $attendance->employee;
        $shift = $employee->shift;

        // Se não há turno definido, não há como determinar atraso.
        if (! $shift) {
            return false;
        }

        // Converte o horário de início do turno para um objeto Carbon.
        $shiftStart = Carbon::parse($shift->start_time);
        // Extrai apenas o horário (HH:MM:SS) do registro de ponto.
        $recordedTime = $attendance->recorded_at->format('H:i:s');
        // Converte o horário registrado para um objeto Carbon no mesmo dia.
        $recordedCarbon = Carbon::parse($recordedTime);

        // Calcula a tolerância permitida (em minutos) para atrasos.
        $toleranceMinutes = $shift->tolerance_minutes ?? 15;
        // Define o limite de atraso como o horário de início mais a tolerância.
        $lateThreshold = $shiftStart->addMinutes($toleranceMinutes);

        // Verifica se o horário registrado é posterior ao limite de tolerância.
        return $recordedCarbon->gt($lateThreshold);
    }

    /**
     * Verifica se é saída antecipada.
     */
    public function isEarlyExit(Attendance $attendance): bool
    {
        // Apenas check-outs podem ser considerados saídas antecipadas.
        if ($attendance->type !== AttendanceType::CheckOut) {
            return false;
        }

        // Se o check-out tem justificativa aprovada, não é considerado antecipado.
        if ($this->isJustified($attendance)) {
            return false;
        }

        // Obtém o funcionário e seu turno para comparar horários.
        $employee = $attendance->employee;
        $shift = $employee->shift;

        // Sem turno definido, não há como avaliar saída antecipada.
        if (! $shift) {
            return false;
        }

        // Converte o horário de fim do turno para um objeto Carbon.
        $shiftEnd = Carbon::parse($shift->end_time);
        // Extrai o horário registrado do check-out.
        $recordedTime = $attendance->recorded_at->format('H:i:s');
        // Cria um objeto Carbon com o horário registrado.
        $recordedCarbon = Carbon::parse($recordedTime);

        // Compara se o horário registrado é anterior ao fim do turno.
        return $recordedCarbon->lt($shiftEnd);
    }

    /**
     * Verifica se tem justificativa aprovada.
     */
    public function isJustified(Attendance $attendance): bool
    {
        return $attendance->employee->justifications()
            ->where(function ($query) use ($attendance) {
                $query->where('attendance_id', $attendance->id)
                    ->orWhere(function ($q) use ($attendance) {
                        $q->whereNull('attendance_id')
                            ->whereDate('absence_date', $attendance->recorded_at->toDateString());
                    });
            })
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Lista ausências em um período.
     */
    public function getAbsences(Employee $employee, Carbon $startDate, Carbon $endDate): Collection
    {
        $absences = collect(); // Coleção para armazenar as ausências encontradas.
        $currentDate = $startDate->copy(); // Data atual no loop, começando do início do período.
        $today = now()->startOfDay(); // Data de hoje para evitar processar dias futuros.

        // Itera dia a dia no período, ignorando datas futuras.
        while ($currentDate->lte($endDate) && $currentDate->lt($today)) {
            // Verifica se há escala específica para o dia ou usa padrão de dias úteis.
            $schedule = $employee->schedules()
                ->whereDate('date', $currentDate->toDateString())
                ->first();

            // Determina se é um dia de trabalho baseado na escala ou se é dia útil.
            $isWorkingDay = $schedule ? $schedule->is_working_day : $currentDate->isWeekday();

            if ($isWorkingDay) {
                // Coleta todos os registros de entrada do dia para análise.
                $hasCheckIn = Attendance::where('employee_id', $employee->id)
                    ->whereDate('recorded_at', $currentDate->toDateString())
                    ->where('type', AttendanceType::CheckIn)
                    ->get();

                // Encontra o primeiro check-in pontual (não atrasado).
                $checkInOnTime = $hasCheckIn->firstWhere(fn ($att) => ! $this->isLate($att));
                // Verifica se há pelo menos um check-in atrasado.
                $hasLateCheckIn = $hasCheckIn->filter(fn ($att) => $this->isLate($att))->isNotEmpty();

                // Coleta todos os registros de saída do dia para validar horários.
                $checkOutsToday = Attendance::where('employee_id', $employee->id)
                    ->whereDate('recorded_at', $currentDate->toDateString())
                    ->where('type', AttendanceType::CheckOut)
                    ->get();

                // Verifica se há saídas antecipadas no dia.
                $hasEarlyExit = $checkOutsToday->filter(fn ($att) => $this->isEarlyExit($att))->isNotEmpty();
                // Verifica se há pelo menos uma saída válida (não antecipada).
                $hasValidCheckout = $checkOutsToday->filter(fn ($att) => ! $this->isEarlyExit($att))->isNotEmpty();

                if (! $hasCheckIn->isNotEmpty() && ! $hasValidCheckout) {
                    $hasJustification = $employee->justifications()
                        ->whereDate('absence_date', $currentDate->toDateString())
                        ->where('status', 'approved')
                        ->exists();

                    $absences->push([
                        'date' => $currentDate->copy(),
                        'type' => $hasJustification ? AbsenceType::Justified : AbsenceType::Absence,
                    ]);
                } elseif ($hasCheckIn->isNotEmpty() && $hasLateCheckIn && ! $hasValidCheckout) {
                    $hasJustification = $employee->justifications()
                        ->whereDate('absence_date', $currentDate->toDateString())
                        ->where('status', 'approved')
                        ->exists();

                    if (! $hasJustification) {
                        $absences->push([
                            'date' => $currentDate->copy(),
                            'type' => AbsenceType::Absence,
                            'reason' => 'Atraso sem justificativa',
                        ]);
                    }
                } elseif ($hasCheckIn->isNotEmpty() && ! $hasValidCheckout) {
                    $hasJustification = $employee->justifications()
                        ->whereDate('absence_date', $currentDate->toDateString())
                        ->where('status', 'approved')
                        ->exists();

                    if (! $hasJustification) {
                        $absences->push([
                            'date' => $currentDate->copy(),
                            'type' => AbsenceType::Absence,
                            'reason' => 'Saída antecipada sem justificativa',
                        ]);
                    }
                    // Caso 4: Há saída antecipada, mesmo com check-out válido.
                } elseif ($hasEarlyExit) {
                    // Verifica justificativa para a saída antecipada.
                    $hasJustification = $employee->justifications()
                        ->whereDate('absence_date', $currentDate->toDateString())
                        ->where('status', 'approved')
                        ->exists();

                    // Se não justificado, registra como ausência parcial.
                    if (! $hasJustification) {
                        $absences->push([
                            'date' => $currentDate->copy(),
                            'type' => AbsenceType::Absence,
                            'reason' => 'Saída antecipada',
                        ]);
                    }
                }
            }

            // Avança para o próximo dia no período.
            $currentDate->addDay();
        }

        // Retorna a coleção completa de ausências encontradas.
        return $absences;
    }

    /**
     * Resumo mensal de attendances.
     */
    public function getMonthlySummary(Employee $employee, int $year, int $month): array
    {
        // Define o início e fim do mês para o período de análise.
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Coleta todos os registros de ponto do funcionário no mês.
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->get();

        // Separa os registros em check-ins e check-outs para análise.
        $checkIns = $attendances->where('type', AttendanceType::CheckIn);
        $checkOuts = $attendances->where('type', AttendanceType::CheckOut);

        $daysWorked = 0; // Contador de dias trabalhados completos (pontual e com saída válida).
        // Itera sobre cada check-in para validar se o dia foi trabalhado completamente.
        foreach ($checkIns as $checkIn) {
            // Obtém a data do check-in para correlacionar com check-outs.
            $checkInDate = $checkIn->recorded_at->format('Y-m-d');

            // Verifica se o check-in foi atrasado.
            $hasLateCheckIn = $this->isLate($checkIn);

            // Verifica se há um check-out válido (não antecipado) no mesmo dia.
            $hasValidCheckout = $checkOuts->contains(function ($checkout) use ($checkInDate) {
                return $checkout->recorded_at->format('Y-m-d') === $checkInDate
                    && ! $this->isEarlyExit($checkout);
            });

            // Conta o dia como trabalhado apenas se pontual e com saída válida.
            if ($hasValidCheckout && ! $hasLateCheckIn) {
                $daysWorked++;
            }
        }

        // Obtém as ausências do período para contar tipos.
        $absences = $this->getAbsences($employee, $startDate, $endDate);
        // Conta ausências não justificadas.
        $absenceCount = $absences->where('type', AbsenceType::Absence)->count();
        // Conta ausências justificadas.
        $justifiedCount = $absences->where('type', AbsenceType::Justified)->count();

        // Conta o total de check-ins atrasados no mês.
        $lateCount = $checkIns->filter(fn ($checkIn) => $this->isLate($checkIn))->count();
        // Conta o total de check-outs antecipados no mês.
        $earlyExitCount = $checkOuts->filter(fn ($checkOut) => $this->isEarlyExit($checkOut))->count();

        // Calcula o total de horas trabalhadas no mês.
        $totalHours = $this->calculateWorkedHours($employee, $startDate, $endDate);

        // Retorna o resumo mensal com todas as métricas calculadas.
        return [
            'days_worked' => $daysWorked,
            'late_count' => $lateCount,
            'early_exit_count' => $earlyExitCount,
            'absence_count' => $absenceCount,
            'justified_count' => $justifiedCount,
            'total_hours' => round($totalHours, 2),
        ];
    }
}
