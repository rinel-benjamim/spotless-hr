<?php

namespace App\Services;

use App\AbsenceType;
use App\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    public function recordAttendance(Employee $employee, AttendanceType $type, ?string $notes = null): Attendance
    {
        return Attendance::create([
            'employee_id' => $employee->id,
            'type' => $type,
            'recorded_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function canCheckIn(Employee $employee, ?Carbon $date = null): bool
    {
        $date = $date ?? now();

        $lastCheckIn = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', $date->toDateString())
            ->where('type', AttendanceType::CheckIn)
            ->exists();

        return ! $lastCheckIn;
    }

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

    public function calculateWorkedHours(Employee $employee, Carbon $startDate, Carbon $endDate): float
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at')
            ->get();

        $totalMinutes = 0;
        $checkIn = null;

        foreach ($attendances as $attendance) {
            if ($attendance->type === AttendanceType::CheckIn) {
                $checkIn = $attendance->recorded_at;
            } elseif ($attendance->type === AttendanceType::CheckOut && $checkIn) {
                $totalMinutes += $checkIn->diffInMinutes($attendance->recorded_at);
                $checkIn = null;
            }
        }

        return $totalMinutes / 60;
    }

    public function calculateDailyWorkedHours(Employee $employee, Carbon $date): float
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('recorded_at', $date->toDateString())
            ->orderBy('recorded_at')
            ->get();

        $totalMinutes = 0;
        $checkIn = null;

        foreach ($attendances as $attendance) {
            if ($attendance->type === AttendanceType::CheckIn) {
                $checkIn = $attendance->recorded_at;
            } elseif ($attendance->type === AttendanceType::CheckOut && $checkIn) {
                $totalMinutes += $checkIn->diffInMinutes($attendance->recorded_at);
                $checkIn = null;
            }
        }

        return $totalMinutes / 60;
    }

    public function isLate(Attendance $attendance): bool
    {
        if ($attendance->type !== AttendanceType::CheckIn) {
            return false;
        }

        if ($this->isJustified($attendance)) {
            return false;
        }

        $employee = $attendance->employee;
        $shift = $employee->shift;

        if (! $shift) {
            return false;
        }

        $shiftStart = Carbon::parse($shift->start_time);
        $recordedTime = $attendance->recorded_at->format('H:i:s');
        $recordedCarbon = Carbon::parse($recordedTime);

        $toleranceMinutes = $shift->tolerance_minutes ?? 15;
        $lateThreshold = $shiftStart->addMinutes($toleranceMinutes);

        return $recordedCarbon->gt($lateThreshold);
    }

    public function isEarlyExit(Attendance $attendance): bool
    {
        if ($attendance->type !== AttendanceType::CheckOut) {
            return false;
        }

        if ($this->isJustified($attendance)) {
            return false;
        }

        $employee = $attendance->employee;
        $shift = $employee->shift;

        if (! $shift) {
            return false;
        }

        $shiftEnd = Carbon::parse($shift->end_time);
        $recordedTime = $attendance->recorded_at->format('H:i:s');
        $recordedCarbon = Carbon::parse($recordedTime);

        return $recordedCarbon->lt($shiftEnd);
    }

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

    public function getAbsences(Employee $employee, Carbon $startDate, Carbon $endDate): Collection
    {
        $absences = collect();
        $currentDate = $startDate->copy();
        $today = now()->startOfDay();

        while ($currentDate->lte($endDate) && $currentDate->lt($today)) {
            $schedule = $employee->schedules()
                ->whereDate('date', $currentDate->toDateString())
                ->first();

            $isWorkingDay = $schedule ? $schedule->is_working_day : $currentDate->isWeekday();

            if ($isWorkingDay) {
                $hasCheckIn = Attendance::where('employee_id', $employee->id)
                    ->whereDate('recorded_at', $currentDate->toDateString())
                    ->where('type', AttendanceType::CheckIn)
                    ->exists();

                if (! $hasCheckIn) {
                    $hasJustification = $employee->justifications()
                        ->whereDate('absence_date', $currentDate->toDateString())
                        ->where('status', 'approved')
                        ->exists();

                    $absences->push([
                        'date' => $currentDate->copy(),
                        'type' => $hasJustification ? AbsenceType::Justified : AbsenceType::Absence,
                    ]);
                }
            }

            $currentDate->addDay();
        }

        return $absences;
    }

    public function getMonthlySummary(Employee $employee, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->get();

        $checkIns = $attendances->where('type', AttendanceType::CheckIn);
        $daysWorked = $checkIns->pluck('recorded_at')->map(fn ($date) => $date->format('Y-m-d'))->unique()->count();

        $lateCount = 0;
        foreach ($checkIns as $checkIn) {
            if ($this->isLate($checkIn)) {
                $lateCount++;
            }
        }

        $checkOuts = $attendances->where('type', AttendanceType::CheckOut);
        $earlyExitCount = 0;
        foreach ($checkOuts as $checkOut) {
            if ($this->isEarlyExit($checkOut)) {
                $earlyExitCount++;
            }
        }

        $absences = $this->getAbsences($employee, $startDate, $endDate);
        $absenceCount = $absences->where('type', AbsenceType::Absence)->count();
        $justifiedCount = $absences->where('type', AbsenceType::Justified)->count();

        $totalHours = $this->calculateWorkedHours($employee, $startDate, $endDate);

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
