import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type Attendance, type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    Building2,
    Calendar,
    Clock,
    UserCheck,
    Users,
} from 'lucide-react';

interface AdminDashboardProps {
    stats: {
        totalEmployees: number;
        activeEmployees: number;
        presentToday: number;
        totalHoursThisMonth: number;
    };
    recentAttendances: Attendance[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const formatDateTime = (dateTime: string) => {
    return new Date(dateTime).toLocaleString('pt-PT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getAttendanceTypeBadge = (type: string) => {
    return type === 'check_in' ? (
        <span className="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
            Entrada
        </span>
    ) : (
        <span className="inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
            Saída
        </span>
    );
};

export default function AdminDashboard({
    stats,
    recentAttendances,
}: AdminDashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard - Administrador" />

            <div className="flex flex-col gap-6 p-6">
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card className="p-6">
                        <div className="flex items-center justify-between space-y-0 pb-2">
                            <h3 className="text-sm font-medium text-muted-foreground">
                                Total de Funcionários
                            </h3>
                            <Users className="size-4 text-muted-foreground" />
                        </div>
                        <div className="text-2xl font-bold">
                            {stats.totalEmployees}
                        </div>
                    </Card>

                    <Card className="p-6">
                        <div className="flex items-center justify-between space-y-0 pb-2">
                            <h3 className="text-sm font-medium text-muted-foreground">
                                Funcionários Ativos
                            </h3>
                            <UserCheck className="size-4 text-muted-foreground" />
                        </div>
                        <div className="text-2xl font-bold">
                            {stats.activeEmployees}
                        </div>
                    </Card>

                    <Card className="p-6">
                        <div className="flex items-center justify-between space-y-0 pb-2">
                            <h3 className="text-sm font-medium text-muted-foreground">
                                Presentes Hoje
                            </h3>
                            <Building2 className="size-4 text-muted-foreground" />
                        </div>
                        <div className="text-2xl font-bold">
                            {stats.presentToday}
                        </div>
                    </Card>

                    <Card className="p-6">
                        <div className="flex items-center justify-between space-y-0 pb-2">
                            <h3 className="text-sm font-medium text-muted-foreground">
                                Horas Totais (Mês)
                            </h3>
                            <Clock className="size-4 text-muted-foreground" />
                        </div>
                        <div className="text-2xl font-bold">
                            {stats.totalHoursThisMonth.toFixed(2)}h
                        </div>
                    </Card>
                </div>

                <Card className="p-6">
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-lg font-semibold">
                            Presenças Recentes
                        </h2>
                        <Link
                            href="/attendances"
                            className="text-sm text-primary hover:underline"
                        >
                            Ver todas
                        </Link>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b">
                                    <th className="pb-3 text-left text-sm font-medium text-muted-foreground">
                                        Funcionário
                                    </th>
                                    <th className="pb-3 text-left text-sm font-medium text-muted-foreground">
                                        Tipo
                                    </th>
                                    <th className="pb-3 text-left text-sm font-medium text-muted-foreground">
                                        Data/Hora
                                    </th>
                                    <th className="pb-3 text-left text-sm font-medium text-muted-foreground">
                                        Notas
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {recentAttendances.length > 0 ? (
                                    recentAttendances.map((attendance) => (
                                        <tr
                                            key={attendance.id}
                                            className="border-b last:border-0"
                                        >
                                            <td className="py-3 text-sm">
                                                {attendance.employee
                                                    ?.full_name || 'N/A'}
                                            </td>
                                            <td className="py-3 text-sm">
                                                {getAttendanceTypeBadge(
                                                    attendance.type,
                                                )}
                                            </td>
                                            <td className="py-3 text-sm">
                                                <div className="flex items-center gap-1 text-muted-foreground">
                                                    <Calendar className="size-3" />
                                                    {formatDateTime(
                                                        attendance.recorded_at,
                                                    )}
                                                </div>
                                            </td>
                                            <td className="py-3 text-sm text-muted-foreground">
                                                {attendance.notes || '-'}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan={4}
                                            className="py-8 text-center text-sm text-muted-foreground"
                                        >
                                            Nenhuma presença registada
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
