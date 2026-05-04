{{-- PDF de Escalas de Trabalho - Mostra a escala mensal de todos os funcionários --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Escala de Serviço - {{ $monthName }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 9px; margin: 0; padding: 5px; }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 5px; margin-bottom: 10px; }
        .title { font-size: 14px; font-weight: bold; color: #1e40af; }
        .info { margin-bottom: 8px; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #f8fafc; text-align: center; padding: 2px; border: 1px solid #e2e8f0; color: #475569; font-size: 6px; width: 12px; }
        td { padding: 2px; border: 1px solid #e2e8f0; text-align: center; height: 20px; overflow: hidden; }
        .employee-name { text-align: left; width: 80px; font-weight: bold; background: #f8fafc; font-size: 7px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .day-off { background: #ffffff; color: #6b7280; font-size: 6px; }
        .working-day { background: #bfdbfe; color: #1e40af; font-size: 6px; }
        .no-schedule { background: #4b5563; color: #ffffff; }
        .shift-name { font-size: 5px; display: block; }
        .footer { margin-top: 10px; text-align: center; color: #94a3b8; font-size: 7px; }
        @page { margin: 0.5cm; size: landscape; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50px; vertical-align: middle; border: none;">
                    <img src="{{ public_path('assets/logo.png') }}" alt="Spotless HR" style="height: 25px; width: auto;">
                </td>
                <td style="vertical-align: middle; padding-left: 10px; border: none;">
                    <span class="title">Spotless HR - Escala de Serviço</span>
                </td>
                <td style="text-align: right; vertical-align: middle; border: none;">
                    <div>{{ $monthName }}</div>
                </td>
            </tr>
        </table>
    </div>

    @if($employee)
        <div class="info"><strong>Funcionário:</strong> {{ $employee->full_name }} ({{ $employee->employee_code }})</div>
    @endif

    @php
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $daysInMonth = $startDate->daysInMonth;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 120px;">Funcionário</th>
                @for($i = 1; $i <= $daysInMonth; $i++)
                    @php $currentDate = \Carbon\Carbon::create($year, $month, $i); @endphp
                    <th style="background: {{ $currentDate->isWeekend() ? '#e2e8f0' : '#f8fafc' }}">
                        {{ $i }}<br>{{ $currentDate->translatedFormat('D') }}
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $empId => $empSchedules)
                @php $emp = $empSchedules->first()->employee; @endphp
                <tr>
                    <td class="employee-name">{{ $emp->full_name }}</td>
                    @for($i = 1; $i <= $daysInMonth; $i++)
                        @php 
                            $currentDate = \Carbon\Carbon::create($year, $month, $i);
                            $schedule = $empSchedules->first(function($s) use ($currentDate) {
                                return $s->date->format('Y-m-d') === $currentDate->format('Y-m-d');
                            });
                        @endphp
                        @if($schedule && $schedule->is_working_day)
                            <td class="working-day">
                                <span style="font-weight: bold;">{{ $schedule->shift->name ?? 'S' }}</span>
                                <span class="shift-name">{{ substr($schedule->shift->start_time ?? '', 0, 5) }}</span>
                            </td>
                        @elseif($schedule && !$schedule->is_working_day)
                            <td class="day-off">FOLGA</td>
                        @else
                            <td class="no-schedule"></td>
                        @endif
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} | Spotless HR - Sistema de Gestão de Recursos Humanos
    </div>
</body>
</html>
