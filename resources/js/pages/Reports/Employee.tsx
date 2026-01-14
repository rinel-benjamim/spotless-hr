import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import {
    type Attendance,
    type BreadcrumbItem,
    type Employee,
    type MonthlySummary,
} from '@/types';
import { Head, router } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { ChevronLeft, ChevronRight, Clock, Download } from 'lucide-react';

interface EmployeeReportProps {
    employee: Employee;
    attendances: Attendance[];
    summary: MonthlySummary;
    year: number;
    month: number;
}

const formatTime = (dateString: string) => {
    return format(new Date(dateString), 'HH:mm', { locale: ptBR });
};

const formatDate = (dateString: string) => {
    return format(new Date(dateString), "dd 'de' MMMM", { locale: ptBR });
};

const getAttendanceTypeLabel = (type: string) => {
    return type === 'check_in' ? 'Entrada' : 'Saída';
};

const getAttendanceTypeBadge = (type: string) => {
    return type === 'check_in' ? (
        <span className="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">
            Entrada
        </span>
    ) : (
        <span className="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-blue-600/20 ring-inset">
            Saída
        </span>
    );
};

export default function EmployeeReport({
    employee,
    attendances,
    summary,
    year,
    month,
}: EmployeeReportProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
            title: 'Relatórios',
            href: '/reports',
        },
        {
            title: employee.full_name,
            href: `/reports/employee/${employee.id}`,
        },
    ];

    const monthName = format(new Date(year, month - 1), 'MMMM yyyy', {
        locale: ptBR,
    });

    const navigateMonth = (direction: number) => {
        let newMonth = month + direction;
        let newYear = year;

        if (newMonth > 12) {
            newMonth = 1;
            newYear++;
        } else if (newMonth < 1) {
            newMonth = 12;
            newYear--;
        }

        router.get(
            `/reports/employee/${employee.id}?year=${newYear}&month=${newMonth}`,
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Relatório - ${employee.full_name}`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">
                            {employee.full_name}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {employee.employee_code} • {employee.shift?.name}
                        </p>
                    </div>
                    <Button variant="outline">
                        <Download className="mr-2 size-4" />
                        Exportar PDF
                    </Button>
                </div>

                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold capitalize">
                        {monthName}
                    </h2>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => navigateMonth(-1)}
                        >
                            <ChevronLeft className="size-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => navigateMonth(1)}
                        >
                            <ChevronRight className="size-4" />
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Card className="p-4">
                        <p className="text-sm font-medium text-muted-foreground">
                            Dias Trabalhados
                        </p>
                        <p className="mt-2 text-3xl font-bold">
                            {summary.days_worked}
                        </p>
                    </Card>
                    <Card className="p-4">
                        <p className="text-sm font-medium text-muted-foreground">
                            Horas Totais
                        </p>
                        <p className="mt-2 text-3xl font-bold">
                            {summary.total_hours}h
                        </p>
                    </Card>
                    <Card className="p-4">
                        <p className="text-sm font-medium text-muted-foreground">
                            Atrasos
                        </p>
                        <p className="mt-2 text-3xl font-bold text-orange-600">
                            {summary.late_count}
                        </p>
                    </Card>
                    <Card className="p-4">
                        <p className="text-sm font-medium text-muted-foreground">
                            Faltas
                        </p>
                        <p className="mt-2 text-3xl font-bold text-red-600">
                            {summary.absence_count}
                        </p>
                    </Card>
                    <Card className="p-4">
                        <p className="text-sm font-medium text-muted-foreground">
                            Justificadas
                        </p>
                        <p className="mt-2 text-3xl font-bold text-green-600">
                            {summary.justified_count}
                        </p>
                    </Card>
                </div>

                <Card className="overflow-hidden">
                    <div className="border-b bg-muted/50 px-6 py-3">
                        <h3 className="font-semibold">Registos de Presença</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-muted/30">
                                <tr>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Data
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Tipo
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Hora
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Notas
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {attendances.map((attendance) => (
                                    <tr
                                        key={attendance.id}
                                        className="hover:bg-muted/50"
                                    >
                                        <td className="px-6 py-4 text-sm">
                                            {formatDate(attendance.recorded_at)}
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            {getAttendanceTypeBadge(
                                                attendance.type,
                                            )}
                                        </td>
                                        <td className="px-6 py-4 font-mono text-sm">
                                            <div className="flex items-center gap-2">
                                                <Clock className="size-4 text-muted-foreground" />
                                                {formatTime(
                                                    attendance.recorded_at,
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-sm text-muted-foreground">
                                            {attendance.notes || '-'}
                                        </td>
                                    </tr>
                                ))}
                                {attendances.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={4}
                                            className="px-6 py-12 text-center text-sm text-muted-foreground"
                                        >
                                            Nenhum registo encontrado para este
                                            mês.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
