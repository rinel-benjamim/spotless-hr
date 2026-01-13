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

    public function getAbsences(Employee $employee, Carbon $startDate, Carbon $endDate): Collection
    {
        $absences = collect();
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($currentDate->isWeekday()) {
                $hasCheckIn = Attendance::where('employee_id', $employee->id)
                    ->whereDate('recorded_at', $currentDate->toDateString())
                    ->where('type', AttendanceType::CheckIn)
                    ->exists();

                if (! $hasCheckIn) {
                    $hasJustification = $employee->justifications()
                        ->whereDate('absence_date', $currentDate->toDateString())
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

        $absences = $this->getAbsences($employee, $startDate, $endDate);
        $absenceCount = $absences->where('type', AbsenceType::Absence)->count();
        $justifiedCount = $absences->where('type', AbsenceType::Justified)->count();

        $totalHours = $this->calculateWorkedHours($employee, $startDate, $endDate);

        return [
            'days_worked' => $daysWorked,
            'late_count' => $lateCount,
            'absence_count' => $absenceCount,
            'justified_count' => $justifiedCount,
            'total_hours' => round($totalHours, 2),
        ];
    }
}
