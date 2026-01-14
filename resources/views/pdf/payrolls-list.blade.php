<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lista de Folhas de Pagamento - {{ $month }}/{{ $year }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.2;
            margin: 0;
            padding: 10px;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
        }
        .report-title {
            font-size: 16px;
            color: #666;
            float: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        .summary-box {
            margin-top: 20px;
            float: right;
            width: 250px;
            border: 1px solid #e2e8f0;
            padding: 10px;
            background-color: #f8fafc;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <span class="company-name">Spotlight HR</span>
        <span class="report-title">Folhas de Pagamento - {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</span>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Funcionário</th>
                <th class="text-right">Salário Base</th>
                <th class="text-right">Dias Trab.</th>
                <th class="text-right">Faltas</th>
                <th class="text-right">Bónus</th>
                <th class="text-right">Descontos</th>
                <th class="text-right">Líquido</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalBase = 0; 
                $totalNet = 0; 
                $totalDeductions = 0;
                $totalBonus = 0;
            @endphp
            @foreach($payrolls as $payroll)
                @php 
                    $totalBase += $payroll->base_salary;
                    $totalNet += $payroll->net_salary;
                    $totalDeductions += $payroll->total_deductions;
                    $totalBonus += $payroll->total_bonus;
                @endphp
                <tr>
                    <td>{{ $payroll->employee->employee_code }}</td>
                    <td class="font-bold">{{ $payroll->employee->full_name }}</td>
                    <td class="text-right">€{{ number_format($payroll->base_salary, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $payroll->total_days_worked }}</td>
                    <td class="text-right">{{ $payroll->absences_count }}</td>
                    <td class="text-right text-green">€{{ number_format($payroll->total_bonus, 2, ',', '.') }}</td>
                    <td class="text-right text-red">€{{ number_format($payroll->total_deductions, 2, ',', '.') }}</td>
                    <td class="text-right font-bold">€{{ number_format($payroll->net_salary, 2, ',', '.') }}</td>
                    <td>{{ $payroll->paid_at ? 'Pago' : 'Pendente' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <div style="font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">RESUMO TOTAL</div>
        <table style="width: 100%; font-size: 12px;">
            <tr>
                <td>Total Salários Base:</td>
                <td class="text-right">€{{ number_format($totalBase, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Bónus:</td>
                <td class="text-right text-green">€{{ number_format($totalBonus, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Descontos:</td>
                <td class="text-right text-red">€{{ number_format($totalDeductions, 2, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold; border-top: 1px solid #e2e8f0;">
                <td style="padding-top: 5px;">TOTAL LÍQUIDO:</td>
                <td class="text-right style="padding-top: 5px;">€{{ number_format($totalNet, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer" style="clear: both;">
        Gerado em {{ now()->format('d/m/Y H:i') }} | Spotlight HR
    </div>
</body>
</html>
