<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo de Vencimento - {{ $payroll->employee->full_name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }
        .document-title {
            font-size: 18px;
            color: #666;
            text-align: right;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .grid {
            width: 100%;
            border-collapse: collapse;
        }
        .grid td {
            padding: 8px 0;
            vertical-align: top;
        }
        .label {
            color: #666;
            font-size: 12px;
            display: block;
        }
        .value {
            font-weight: 500;
            font-size: 14px;
        }
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .financial-table th {
            text-align: left;
            background-color: #f8fafc;
            padding: 10px;
            font-size: 12px;
            color: #666;
            border-bottom: 1px solid #e2e8f0;
        }
        .financial-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .text-right {
            text-align: right;
        }
        .text-green {
            color: #16a34a;
        }
        .text-red {
            color: #dc2626;
        }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 16px !important;
        }
        .footer {
            margin-top: 50px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
        .status-paid {
            display: inline-block;
            padding: 4px 8px;
            background-color: #dcfce7;
            color: #166534;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            display: inline-block;
            padding: 4px 8px;
            background-color: #fef9c3;
            color: #854d0e;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%">
            <tr>
                <td><span class="company-name">Spotlight HR</span></td>
                <td class="text-right">
                    <span class="document-title">Recibo de Vencimento</span><br>
                    <span style="font-size: 14px; color: #94a3b8">#{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Informações do Funcionário</div>
        <table class="grid">
            <tr>
                <td style="width: 40%">
                    <span class="label">Nome Completo</span>
                    <span class="value">{{ $payroll->employee->full_name }}</span>
                </td>
                <td style="width: 30%">
                    <span class="label">Código</span>
                    <span class="value">{{ $payroll->employee->employee_code }}</span>
                </td>
                <td style="width: 30%">
                    <span class="label">Mês de Referência</span>
                    <span class="value">{{ \Carbon\Carbon::parse($payroll->reference_month)->translatedFormat('F Y') }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Cargo</span>
                    <span class="value">{{ ucfirst($payroll->employee->role->value) }}</span>
                </td>
                <td>
                    <span class="label">Turno</span>
                    <span class="value">{{ $payroll->employee->shift->name ?? 'N/A' }}</span>
                </td>
                <td>
                    <span class="label">Estado de Pagamento</span>
                    <span class="value">
                        @if($payroll->paid_at)
                            <span class="status-paid">PAGO EM {{ \Carbon\Carbon::parse($payroll->paid_at)->format('d/m/Y') }}</span>
                        @else
                            <span class="status-pending">PENDENTE</span>
                        @endif
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Resumo de Atividade</div>
        <table class="grid">
            <tr>
                <td style="width: 25%">
                    <span class="label">Dias Trabalhados</span>
                    <span class="value">{{ $payroll->total_days_worked }}</span>
                </td>
                <td style="width: 25%">
                    <span class="label">Faltas</span>
                    <span class="value">{{ $payroll->absences_count }}</span>
                </td>
                <td style="width: 25%">
                    <span class="label">Atrasos</span>
                    <span class="value">{{ $payroll->late_count }}</span>
                </td>
                <td style="width: 25%">
                    &nbsp;
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalhamento Financeiro</div>
        <table class="financial-table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th class="text-right">Vencimentos</th>
                    <th class="text-right">Descontos</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Salário Base</td>
                    <td class="text-right">Kz {{ number_format($payroll->base_salary, 2, ',', '.') }}</td>
                    <td class="text-right">-</td>
                </tr>
                @if($payroll->total_bonus > 0)
                <tr>
                    <td>Bónus / Extras</td>
                    <td class="text-right text-green">+ Kz {{ number_format($payroll->total_bonus, 2, ',', '.') }}</td>
                    <td class="text-right">-</td>
                </tr>
                @endif
                @if($payroll->total_deductions > 0)
                <tr>
                    <td>Descontos (Faltas e Atrasos)</td>
                    <td class="text-right">-</td>
                    <td class="text-right text-red">- Kz {{ number_format($payroll->total_deductions, 2, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>LÍQUIDO A RECEBER</td>
                    <td colspan="2" class="text-right text-green">Kz {{ number_format($payroll->net_salary, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($payroll->notes)
    <div class="section">
        <div class="section-title">Observações</div>
        <div style="font-size: 12px; background-color: #f8fafc; padding: 10px; border-radius: 4px;">
            {{ $payroll->notes }}
        </div>
    </div>
    @endif

    <div style="margin-top: 80px;">
        <table style="width: 100%">
            <tr>
                <td style="width: 45%; border-top: 1px solid #333; text-align: center; padding-top: 10px; font-size: 12px;">
                    Assinatura da Empresa
                </td>
                <td style="width: 10%">&nbsp;</td>
                <td style="width: 45%; border-top: 1px solid #333; text-align: center; padding-top: 10px; font-size: 12px;">
                    Assinatura do Funcionário
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Gerado automaticamente por Spotlight HR em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
