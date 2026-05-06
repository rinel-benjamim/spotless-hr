<?php

namespace App\Http\Controllers;

use App\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Justification;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

/**
 * Classe responsável por DashboardController.
 */
class DashboardController extends Controller
{
    /**
     * Redireciona para o dashboard correto conforme o tipo de usuário.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isManager()) {
            return $this->managerDashboard();
        }

        return $this->employeeDashboard();
    }

    /**
     * Dashboard do Administrador (Diretor) - métricas completas.
     */
    protected function adminDashboard()
    {
        $stats = $this->getAdminStats();

        $recentAttendances = Attendance::with('employee')
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        $pendingJustificationsCount = Justification::where('status', 'pending')->count();

        return Inertia::render('Dashboard/Admin', [
            'stats' => $stats,
            'recentAttendances' => $recentAttendances,
            'dashboardTitle' => 'Dashboard do Diretor',
            'pendingJustificationsCount' => $pendingJustificationsCount,
        ]);
    }

    /**
     * Dashboard do Gerente - métricas limitadas.
     */
    protected function managerDashboard()
    {
        $stats = $this->getManagerStats();

        $employees = Employee::where('status', 'active')
            ->with('shift')
            ->get();

        $recentAttendances = Attendance::with('employee')
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard/Manager', [
            'stats' => $stats,
            'employees' => $employees,
            'recentAttendances' => $recentAttendances,
            'dashboardTitle' => 'Dashboard do Gerente',
        ]);
    }

    /**
     * Calcula métricas para o dashboard do gerente.
     */
    private function getManagerStats()
    {
        // Define o início do mês atual para cálculos mensais.
        $thisMonth = now()->startOfMonth();

        // Conta o total de funcionários ativos na empresa.
        $activeEmployees = Employee::where('status', 'active')->count();

        // Calcula quantos funcionários estão presentes hoje.
        $presentToday = $this->getPresentTodayCount();

        // Reúne todos os eventos de ponto do mês para calcular as horas trabalhadas.
        $events = Attendance::whereDate('recorded_at', '>=', $thisMonth)
            ->orderBy('employee_id') // Ordena por funcionário para agrupar eventos.
            ->orderBy('recorded_at') // Ordena cronologicamente dentro de cada funcionário.
            ->get();

        $totalMinutes = 0; // Acumulador total de minutos trabalhados no mês.
        $currentCheckIn = null; // Armazena o check-in atual para emparelhar com check-out.
        $lastEmployeeId = null; // Controla mudança de funcionário para resetar emparelhamento.

        // Itera sobre cada evento de ponto para calcular horas trabalhadas.
        foreach ($events as $event) {
            // Se mudou de funcionário, reseta o emparelhamento de check-in/check-out.
            if ($lastEmployeeId !== $event->employee_id) {
                $currentCheckIn = null; // Limpa check-in anterior.
                $lastEmployeeId = $event->employee_id; // Atualiza ID do funcionário atual.
            }

            // Se é check-in, armazena para emparelhar com próximo check-out.
            if ($event->type === AttendanceType::CheckIn) {
                $currentCheckIn = $event->recorded_at;
            }
            // Se é check-out e há check-in emparelhado, calcula diferença de tempo.
            elseif ($event->type === AttendanceType::CheckOut && $currentCheckIn) {
                // Calcula minutos trabalhados entre entrada e saída.
                $totalMinutes += $currentCheckIn->diffInMinutes($event->recorded_at);
                $currentCheckIn = null; // Limpa check-in após cálculo.
            }
        }

        // Converte minutos totais para horas (sem arredondamento ainda).
        $totalHoursThisMonth = $totalMinutes / 60;

        // Retorna array com todas as métricas calculadas para o gerente.
        return [
            'activeEmployees' => $activeEmployees,
            'presentToday' => $presentToday,
            'totalHoursThisMonth' => round($totalHoursThisMonth, 2), // Arredonda para 2 casas decimais.
            'monthName' => now()->translatedFormat('F Y'), // Nome do mês atual em português.
        ];
    }

    /**
     * Conta funcionários presentes hoje (com check-out válido).
     */
    private function getPresentTodayCount(): int
    {
        // Define a data de hoje para filtrar registros.
        $today = now()->toDateString();
        // Instancia o serviço de attendance para validações.
        $attendanceService = app(\App\Services\AttendanceService::class);

        // Busca os IDs únicos dos funcionários que fizeram check-in hoje.
        $employeesWithCheckIn = Attendance::whereDate('recorded_at', $today)
            ->where('type', AttendanceType::CheckIn)
            ->pluck('employee_id') // Extrai apenas os IDs dos funcionários.
            ->unique(); // Remove duplicatas.

        $presentToday = 0; // Contador de funcionários presentes hoje.
        // Itera sobre cada funcionário que fez check-in hoje.
        foreach ($employeesWithCheckIn as $employeeId) {
            // Carrega o funcionário completo do banco.
            $employee = Employee::find($employeeId);
            // Se funcionário não existe ou não tem turno definido, conta como presente.
            if (! $employee || ! $employee->shift) {
                $presentToday++;

                continue; // Pula para o próximo funcionário.
            }

            // Verifica se há check-outs válidos (não antecipados) para este funcionário hoje.
            $hasValidCheckout = Attendance::where('employee_id', $employeeId)
                ->whereDate('recorded_at', $today)
                ->where('type', AttendanceType::CheckOut)
                ->get() // Busca todos os check-outs do dia.
                ->filter(fn ($att) => ! $attendanceService->isEarlyExit($att)) // Filtra apenas válidos.
                ->isNotEmpty(); // Verifica se há pelo menos um válido.

            // Se tem check-out válido, conta como presente.
            if ($hasValidCheckout) {
                $presentToday++;
            }
        }

        // Retorna o total de funcionários considerados presentes hoje.
        return $presentToday;
    }

    /**
     * Exporta KPIs do dashboard para PDF.
     */
    public function exportKpis()
    {
        if (! auth()->user()->canViewAllData()) {
            abort(403);
        }

        $stats = $this->getAdminStats();
        $pdf = Pdf::loadView('pdf.dashboard-kpis', compact('stats'));

        return $pdf->download('Dashboard_KPIs_'.now()->format('Y-m').'.pdf');
    }

    /**
     * Calcula métricas completas para o dashboard do administrador.
     */
    private function getAdminStats()
    {
        $thisMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();

        $presentToday = $this->getPresentTodayCount();

        // Reúne entradas e saídas do mês para calcular horas totais do período.
        $events = Attendance::whereDate('recorded_at', '>=', $thisMonth)
            ->orderBy('employee_id')
            ->orderBy('recorded_at')
            ->get();

        $totalMinutes = 0;
        $currentCheckIn = null;
        $lastEmployeeId = null;

        foreach ($events as $event) {
            if ($lastEmployeeId !== $event->employee_id) {
                $currentCheckIn = null;
                $lastEmployeeId = $event->employee_id;
            }

            if ($event->type === AttendanceType::CheckIn) {
                $currentCheckIn = $event->recorded_at;
            } elseif ($event->type === AttendanceType::CheckOut && $currentCheckIn) {
                $totalMinutes += $currentCheckIn->diffInMinutes($event->recorded_at);
                $currentCheckIn = null;
            }
        }

        $totalHoursThisMonth = $totalMinutes / 60;

        $daysInMonth = now()->daysInMonth;
        $currentDay = now()->day;
        $averageDailyAttendance = $currentDay > 0
            ? round($events->where('type', AttendanceType::CheckIn)->groupBy(fn ($e) => $e->recorded_at->format('Y-m-d'))->count() / $currentDay, 1)
            : 0;

        $attendanceService = new \App\Services\AttendanceService;
        $allEmployees = Employee::where('status', 'active')->get();
        $totalAbsences = 0;
        $totalLates = 0;

        foreach ($allEmployees as $employee) {
            $summary = $attendanceService->getMonthlySummary($employee, now()->year, now()->month);
            $totalAbsences += $summary['absence_count'];
            $totalLates += $summary['late_count'];
        }

        $averageHoursPerEmployee = $activeEmployees > 0
            ? round($totalHoursThisMonth / $activeEmployees, 1)
            : 0;

        return [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'presentToday' => $presentToday,
            'totalHoursThisMonth' => round($totalHoursThisMonth, 2),
            'monthName' => now()->translatedFormat('F Y'),
            'averageDailyAttendance' => $averageDailyAttendance,
            'averageHoursPerEmployee' => $averageHoursPerEmployee,
            'totalAbsencesThisMonth' => $totalAbsences,
            'totalLatesThisMonth' => $totalLates,
        ];
    }

    /**
     * Dashboard do Funcionário - histórico de ponto pessoal.
     */
    protected function employeeDashboard()
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return Inertia::render('Dashboard/Employee', [
                'message' => 'Perfil de funcionário não encontrado.',
            ]);
        }

        $thisMonth = now()->startOfMonth();

        $lastAttendance = Attendance::where('employee_id', $employee->id)
            ->latest('recorded_at')
            ->first();

        $todayAttendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', now())
            ->get();

        $monthAttendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', '>=', $thisMonth)
            ->orderBy('recorded_at', 'desc')
            ->get();

        return Inertia::render('Dashboard/Employee', [
            'employee' => $employee->load('shift'),
            'lastAttendance' => $lastAttendance,
            'todayAttendances' => $todayAttendances,
            'monthAttendances' => $monthAttendances,
            'dashboardTitle' => 'Dashboard do Funcionário',
        ]);
    }
}
