<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Funcionário - {{ $employee->full_name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 11px; padding: 20px; }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #1e40af; }
        .employee-info { margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .summary-grid { width: 100%; margin-bottom: 20px; }
        .summary-card { background: #fff; border: 1px solid #e2e8f0; padding: 10px; text-align: center; }
        .summary-label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .summary-value { font-size: 16px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; text-align: left; padding: 8px; border-bottom: 1px solid #e2e8f0; color: #64748b; }
        td { padding: 8px; border-bottom: 1px solid #f1f5f9; }
        .footer { margin-top: 30px; text-align: center; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <span class="title">Relatório de Frequência Individual</span>
        <div style="float: right;">{{ $monthName }}</div>
        <div style="clear: both;"></div>
    </div>

    <div class="employee-info">
        <table width="100%">
            <tr>
                <td style="border:0; padding:0;">
                    <strong>Funcionário:</strong> {{ $employee->full_name }}<br>
                    <strong>Código:</strong> {{ $employee->employee_code }}<br>
                    <strong>Cargo:</strong> {{ ucfirst($employee->role->value) }}
                </td>
                <td style="border:0; padding:0; text-align: right;">
                    <strong>Turno:</strong> {{ $employee->shift->name ?? 'N/A' }}<br>
                    <strong>Horário:</strong> {{ $employee->shift ? $employee->shift->start_time . ' - ' . $employee->shift->end_time : '-' }}
                </td>
            </tr>
        </table>
    </div>

    <table class="summary-grid" cellspacing="10">
        <tr>
            <td>
                <div class="summary-card">
                    <div class="summary-label">Dias Trabalhados</div>
                    <div class="summary-value">{{ $summary['days_worked'] }}</div>
                </div>
            </td>
            <td>
                <div class="summary-card">
                    <div class="summary-label">Faltas</div>
                    <div class="summary-value text-red">{{ $summary['absence_count'] }}</div>
                </div>
            </td>
            <td>
                <div class="summary-card">
                    <div class="summary-label">Atrasos</div>
                    <div class="summary-value">{{ $summary['late_count'] }}</div>
                </div>
            </td>
            <td>
                <div class="summary-card">
                    <div class="summary-label">Horas Totais</div>
                    <div class="summary-value">{{ number_format($summary['total_hours'], 1) }}h</div>
                </div>
            </td>
        </tr>
    </table>

    <div style="font-weight: bold; margin-bottom: 10px; font-size: 12px;">Registos de Ponto</div>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Hora</th>
                <th>Tipo</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->recorded_at)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->recorded_at)->format('H:i') }}</td>
                    <td>{{ $attendance->type->value === 'check_in' ? 'Entrada' : 'Saída' }}</td>
                    <td>{{ $attendance->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} | Spotlight HR
    </div>
</body>
</html>
