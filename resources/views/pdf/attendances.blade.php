<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Presenças - Spotlight HR</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 11px; padding: 20px; }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #1e40af; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; text-align: left; padding: 8px; border-bottom: 1px solid #e2e8f0; color: #64748b; }
        td { padding: 8px; border-bottom: 1px solid #f1f5f9; }
        .badge { padding: 2px 6px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-in { background: #dcfce7; color: #166534; }
        .badge-out { background: #dbeafe; color: #1e40af; }
        .footer { margin-top: 30px; text-align: center; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <span class="title">Spotlight HR - Relatório de Presenças</span>
        <div style="float: right;">Gerado em {{ now()->format('d/m/Y H:i') }}</div>
        <div style="clear: both;"></div>
    </div>

    <div class="info">
        @if($employee)
            <strong>Funcionário:</strong> {{ $employee->full_name }} ({{ $employee->employee_code }})<br>
        @else
            <strong>Funcionário:</strong> Todos<br>
        @endif
        
        <strong>Período:</strong> 
        {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Início' }} 
        até 
        {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Hoje' }}
    </div>

    <table>
        <thead>
            <tr>
                @if(!$employee) <th>Funcionário</th> @endif
                <th>Tipo</th>
                <th>Data</th>
                <th>Hora</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
                <tr>
                    @if(!$employee) <td>{{ $attendance->employee->full_name }}</td> @endif
                    <td>
                        <span class="badge {{ $attendance->type === 'check_in' ? 'badge-in' : 'badge-out' }}">
                            {{ $attendance->type === 'check_in' ? 'Entrada' : 'Saída' }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($attendance->recorded_at)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->recorded_at)->format('H:i') }}</td>
                    <td>{{ $attendance->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Spotlight HR - Gestão de Presenças
    </div>
</body>
</html>
