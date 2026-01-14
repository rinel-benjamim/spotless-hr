<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Calendário de Presenças - {{ $employee->full_name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 10px; padding: 20px; }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #1e40af; }
        .calendar { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .calendar th { background: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; color: #64748b; }
        .calendar td { border: 1px solid #e2e8f0; vertical-align: top; padding: 5px; height: 80px; }
        .day-number { font-weight: bold; margin-bottom: 5px; display: block; }
        .event { font-size: 8px; margin-bottom: 2px; padding: 2px; border-radius: 2px; }
        .check-in { background: #dcfce7; color: #166534; }
        .check-out { background: #dbeafe; color: #1e40af; }
        .absence { background: #fee2e2; color: #991b1b; }
        .other-month { background: #f8fafc; }
        .footer { margin-top: 30px; text-align: center; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <span class="title">Calendário de Presenças Individual</span>
        <div style="float: right;">{{ $monthName }} - {{ $employee->full_name }}</div>
        <div style="clear: both;"></div>
    </div>

    @php
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        $dayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sun) to 6 (Sat)
        // Adjust for Monday start if needed, but keeping standard for simplicity
        $currentDay = 1;
    @endphp

    <table class="calendar">
        <thead>
            <tr>
                <th>Dom</th>
                <th>Seg</th>
                <th>Ter</th>
                <th>Qua</th>
                <th>Qui</th>
                <th>Sex</th>
                <th>Sáb</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < 6; $i++)
                <tr>
                    @for ($j = 0; $j < 7; $j++)
                        @php $cellIndex = $i * 7 + $j; @endphp
                        @if ($cellIndex >= $dayOfWeek && $currentDay <= $daysInMonth)
                            @php 
                                $dateStr = \Carbon\Carbon::create($year, $month, $currentDay)->toDateString();
                                $dayAttendances = $attendances->get($dateStr, collect());
                                $dayAbsence = collect($absences)->firstWhere('date', $dateStr);
                            @endphp
                            <td>
                                <span class="day-number">{{ $currentDay }}</span>
                                @foreach($dayAttendances as $att)
                                    <div class="event {{ $att->type === 'check_in' ? 'check-in' : 'check-out' }}">
                                        {{ $att->type === 'check_in' ? 'ENT' : 'SAÍ' }}: {{ \Carbon\Carbon::parse($att->recorded_at)->format('H:i') }}
                                    </div>
                                @endforeach
                                @if($dayAbsence)
                                    <div class="event absence">FALTA</div>
                                @endif
                            </td>
                            @php $currentDay++; @endphp
                        @else
                            <td class="other-month"></td>
                        @endif
                    @endfor
                </tr>
                @if ($currentDay > $daysInMonth) @break @endif
            @endfor
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} | Spotlight HR
    </div>
</body>
</html>
