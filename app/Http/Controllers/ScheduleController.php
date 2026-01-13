<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
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

        $schedules = Schedule::with(['employee', 'shift'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('employee_id')
            ->get()
            ->groupBy('employee_id');

        $employees = Employee::where('status', 'active')->get();

        return Inertia::render('Schedules/Index', [
            'schedules' => $schedules,
            'employees' => $employees,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        $shifts = Shift::all();

        return Inertia::render('Schedules/Create', [
            'employees' => $employees,
            'shifts' => $shifts,
        ]);
    }

    public function store(StoreScheduleRequest $request)
    {
        $data = $request->validated();

        if (isset($data['generate_month'])) {
            $this->generateMonthSchedule(
                $data['employee_id'],
                $data['year'],
                $data['month'],
                $data['shift_id'] ?? null
            );

            return redirect()->route('schedules.index', [
                'year' => $data['year'],
                'month' => $data['month'],
            ])->with('success', 'Escala mensal gerada com sucesso.');
        }

        Schedule::create($data);

        return redirect()->route('schedules.index')
            ->with('success', 'Escala criada com sucesso.');
    }

    public function update(Request $request, Schedule $schedule)
    {
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
        $schedule->delete();

        return redirect()->back()
            ->with('success', 'Escala removida com sucesso.');
    }

    protected function generateMonthSchedule(int $employeeId, int $year, int $month, ?int $shiftId): void
    {
        $employee = Employee::findOrFail($employeeId);
        $shiftId = $shiftId ?? $employee->shift_id;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            Schedule::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $date,
                ],
                [
                    'shift_id' => $shiftId,
                    'is_working_day' => $date->isWeekday(),
                ]
            );
        }
    }
}
