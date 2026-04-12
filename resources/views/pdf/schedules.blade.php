<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Escala de Serviço - {{ $monthName }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 10px; margin: 0; padding: 10px; }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 5px; margin-bottom: 15px; }
        .title { font-size: 16px; font-weight: bold; color: #1e40af; }
        .info { margin-bottom: 10px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #f8fafc; text-align: center; padding: 4px; border: 1px solid #e2e8f0; color: #475569; font-size: 8px; }
        td { padding: 4px; border: 1px solid #e2e8f0; text-align: center; height: 30px; }
        .employee-name { text-align: left; width: 120px; font-weight: bold; background: #f8fafc; }
        .day-off { background: #2563eb; color: #ffffff; }
        .working-day { background: #16a34a; color: #ffffff; }
        .no-schedule { background: #ffffff; color: #6b7280; }
        .shift-name { font-size: 7px; display: block; }
        .footer { margin-top: 15px; text-align: center; color: #94a3b8; font-size: 8px; }
        @page { margin: 1cm; }
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
