<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de KPIs - Spotlight HR</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; padding: 30px; }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 15px; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; color: #1e40af; }
        .date { float: right; color: #64748b; }
        .grid { width: 100%; margin-bottom: 30px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; text-align: center; border-radius: 8px; }
        .card-title { font-size: 12px; color: #64748b; text-transform: uppercase; margin-bottom: 10px; }
        .card-value { font-size: 28px; font-weight: bold; color: #0f172a; }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; border-left: 4px solid #3b82f6; padding-left: 10px; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <span class="title">Spotlight HR</span>
        <span class="date">{{ now()->format('d/m/Y H:i') }}</span>
    </div>

    <div class="section-title">Indicadores de Desempenho ({{ $stats['monthName'] }})</div>
    
    <table class="grid" cellspacing="10">
        <tr>
            <td width="50%">
                <div class="card">
                    <div class="card-title">Total de Funcionários</div>
                    <div class="card-value">{{ $stats['totalEmployees'] }}</div>
                </div>
            </td>
            <td width="50%">
                <div class="card">
                    <div class="card-title">Funcionários Ativos</div>
                    <div class="card-value">{{ $stats['activeEmployees'] }}</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="card">
                    <div class="card-title">Presentes Hoje</div>
                    <div class="card-value">{{ $stats['presentToday'] }}</div>
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="card-title">Horas Trabalhadas (Mês)</div>
                    <div class="card-value">{{ number_format($stats['totalHoursThisMonth'], 1) }}h</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Análise de Eficiência</div>
    <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <table width="100%">
            <tr>
                <td width="70%">Taxa de Presença (Hoje):</td>
                <td width="30%" align="right" style="font-weight: bold;">
                    {{ $stats['activeEmployees'] > 0 ? round(($stats['presentToday'] / $stats['activeEmployees']) * 100) : 0 }}%
                </td>
            </tr>
            <tr style="height: 10px;"><td></td></tr>
            <tr>
                <td>Média de Horas por Funcionário:</td>
                <td align="right" style="font-weight: bold;">
                    {{ $stats['activeEmployees'] > 0 ? number_format($stats['totalHoursThisMonth'] / $stats['activeEmployees'], 1) : 0 }}h
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Este relatório contém dados consolidados até o momento de sua geração.<br>
        Spotlight HR - Sistema de Gestão de Recursos Humanos
    </div>
</body>
</html>
