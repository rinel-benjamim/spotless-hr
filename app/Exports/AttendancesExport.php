<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class AttendancesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Attendance::query()->with(['employee']);

        if ($this->request->filled('employee_id')) {
            $query->where('employee_id', $this->request->employee_id);
        }

        if ($this->request->filled('start_date')) {
            $query->whereDate('recorded_at', '>=', $this->request->start_date);
        }

        if ($this->request->filled('end_date')) {
            $query->whereDate('recorded_at', '<=', $this->request->end_date);
        }

        return $query->latest('recorded_at');
    }

    public function headings(): array
    {
        return [
            'Funcionário',
            'Cód. Funcionário',
            'Tipo',
            'Data/Hora',
            'Notas',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->employee->full_name,
            $attendance->employee->employee_code,
            $attendance->type === 'check_in' ? 'Entrada' : 'Saída',
            $attendance->recorded_at->format('d/m/Y H:i'),
            $attendance->notes,
        ];
    }
}
