<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $query = Schedule::with(['employee', 'shift'])
            ->whereBetween('date', [$startDate, $endDate]);

        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            $query->where('employee_id', auth()->user()->employee->id);
        }

        $schedules = $query->orderBy('date')
            ->orderBy('employee_id')
            ->get()
            ->groupBy('employee_id');

        $employees = (auth()->user()->isAdmin() || auth()->user()->employee?->isManager())
            ? Employee::where('status', 'active')->get()
            : Employee::where('id', auth()->user()->employee->id)->get();

        return Inertia::render('Schedules/Index', [
            'schedules' => $schedules,
            'employees' => $employees,
            'year' => (int) $year,
            'month' => (int) $month,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $employeeId = $request->input('employee_id');

        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            $employeeId = auth()->user()->employee->id;
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $query = Schedule::with(['employee', 'shift'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('employee_id');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $schedules = $query->get()->groupBy('employee_id');
        $employee = $employeeId ? Employee::find($employeeId) : null;
        $monthName = Carbon::create($year, $month, 1)->translatedFormat('F Y');

        $pdf = Pdf::loadView('pdf.schedules', compact('schedules', 'year', 'month', 'employee', 'monthName'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("escala-{$monthName}.pdf");
    }

    public function create()
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $employees = Employee::where('status', 'active')->get();
        $shifts = Shift::all();

        return Inertia::render('Schedules/Create', [
            'employees' => $employees,
            'shifts' => $shifts,
        ]);
    }

    public function store(StoreScheduleRequest $request)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $data = $request->validated();

        foreach ($data['employee_ids'] as $employeeId) {
            if (filter_var($data['generate_month'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $this->generateMonthSchedule(
                    $employeeId,
                    $data['year'],
                    $data['month'],
                    $data['shift_id'] ?? null
                );
            } else {
                Schedule::create(array_merge($data, ['employee_id' => $employeeId]));
            }
        }

        $message = filter_var($data['generate_month'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ? 'Escalas mensais geradas com sucesso.'
            : 'Escalas criadas com sucesso.';

        return redirect()->route('schedules.index', [
            'year' => $data['year'] ?? now()->year,
            'month' => $data['month'] ?? now()->month,
        ])->with('success', $message);
    }

    public function update(Request $request, Schedule $schedule)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $data = $request->validate([
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'is_working_day' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule->update($data);

        return redirect()->back()
            ->with('success', 'Escala atualizada com sucesso.');
    }

    public function destroy(Schedule $schedule)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->employee?->isManager()) {
            abort(403);
        }

        $schedule->delete();

        return redirect()->back()
            ->with('success', 'Escala removida com sucesso.');
    }

    protected function generateMonthSchedule(int $employeeId, int $year, int $month, ?int $shiftId): void
    {
        $employee = Employee::findOrFail($employeeId);
        $shiftId = $shiftId ?: $employee->shift_id; // Usar ?: em vez de ??

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            Schedule::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'shift_id' => $shiftId,
                    'is_working_day' => $date->isWeekday(),
                ]
            );
        }
    }
}
