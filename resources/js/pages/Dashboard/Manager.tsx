import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type Attendance, type BreadcrumbItem, type Employee } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import {
    ArrowRight,
    Clock,
    FileText,
    LogIn,
    LogOut,
    TrendingUp,
    UserCheck,
    Users,
} from 'lucide-react';
import { useState } from 'react';

interface ManagerDashboardProps {
    stats: {
        activeEmployees: number;
        presentToday: number;
        totalHoursThisMonth: number;
        monthName: string;
    };
    employees: Employee[];
    recentAttendances: Attendance[];
    dashboardTitle: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

export default function ManagerDashboard({
    stats,
    employees,
    recentAttendances,
    dashboardTitle,
}: ManagerDashboardProps) {
    const [selectedEmployeeId, setSelectedEmployeeId] = useState<string>('');

    const handleCheckInEmployee = () => {
        if (!selectedEmployeeId) return;
        router.post('/attendances/check-in-employee', {
            employee_id: parseInt(selectedEmployeeId),
        });
    };

    const handleCheckOutEmployee = () => {
        if (!selectedEmployeeId) return;
        router.post('/attendances/check-out-employee', {
            employee_id: parseInt(selectedEmployeeId),
        });
    };

    const statCards = [
        {
            title: 'Funcionários Ativos',
            value: stats.activeEmployees,
            icon: UserCheck,
            color: 'text-secondary',
            bgColor: 'bg-secondary/10',
        },
        {
            title: 'Presentes Hoje',
            value: stats.presentToday,
            icon: TrendingUp,
            color: 'text-accent-foreground',
            bgColor: 'bg-accent/20',
        },
        {
            title: 'Horas Trabalhadas (Mês)',
            value: `${Number(stats.totalHoursThisMonth).toFixed(1)}h`,
            icon: Clock,
            color: 'text-primary',
            bgColor: 'bg-primary/10',
        },
    ];

    const getAttendanceTypeBadge = (type: string) => {
        return type === 'check_in' ? (
            <Badge variant="default" className="bg-green-600 text-white">
                Entrada
            </Badge>
        ) : (
            <Badge variant="secondary" className="bg-blue-600 text-white">
                Saída
            </Badge>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">{dashboardTitle}</h1>
                        <p className="text-muted-foreground">
                            Gestão de equipe e presenças
                        </p>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    {statCards.map((stat, index) => (
                        <Card key={index} className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">
                                        {stat.title}
                                    </p>
                                    <p className="mt-2 text-3xl font-bold">
                                        {stat.value}
                                    </p>
                                </div>
                                <div
                                    className={`rounded-lg p-3 ${stat.bgColor}`}
                                >
                                    <stat.icon
                                        className={`size-6 ${stat.color}`}
                                    />
                                </div>
                            </div>
                        </Card>
                    ))}
                </div>

                <Card className="p-6">
                    <h2 className="mb-4 text-lg font-semibold">
                        Registar Presença de Funcionário
                    </h2>
                    <div className="flex flex-wrap items-end gap-4">
                        <div className="w-64">
                            <Select
                                value={selectedEmployeeId}
                                onValueChange={setSelectedEmployeeId}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione o funcionário" />
                                </SelectTrigger>
                                <SelectContent>
                                    {employees.map((employee) => (
                                        <SelectItem
                                            key={employee.id}
                                            value={employee.id.toString()}
                                        >
                                            {employee.full_name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="flex gap-2">
                            <Button
                                onClick={handleCheckInEmployee}
                                disabled={!selectedEmployeeId}
                                className="bg-green-600 hover:bg-green-700"
                            >
                                <LogIn className="mr-2 size-4" />
                                Entrada
                            </Button>
                            <Button
                                onClick={handleCheckOutEmployee}
                                disabled={!selectedEmployeeId}
                                variant="secondary"
                            >
                                <LogOut className="mr-2 size-4" />
                                Saída
                            </Button>
                        </div>
                    </div>
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card className="p-6">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold">
                                Atividade Recente
                            </h2>
                            <Link href="/attendances">
                                <Button variant="ghost" size="sm">
                                    Ver todas
                                    <ArrowRight className="ml-2 size-4" />
                                </Button>
                            </Link>
                        </div>

                        {recentAttendances.length === 0 ? (
                            <div className="py-8 text-center text-sm text-muted-foreground">
                                Nenhuma atividade recente
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {recentAttendances
                                    .slice(0, 8)
                                    .map((attendance) => (
                                        <div
                                            key={attendance.id}
                                            className="flex items-center justify-between rounded-lg border p-3"
                                        >
                                            <div className="flex items-center gap-3">
                                                <div>
                                                    <p className="font-medium">
                                                        {
                                                            attendance.employee
                                                                ?.full_name
                                                        }
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {format(
                                                            new Date(
                                                                attendance.recorded_at,
                                                            ),
                                                            "dd/MM/yyyy 'às' HH:mm",
                                                            { locale: ptBR },
                                                        )}
                                                    </p>
                                                </div>
                                            </div>
                                            {getAttendanceTypeBadge(
                                                attendance.type,
                                            )}
                                        </div>
                                    ))}
                            </div>
                        )}
                    </Card>

                    <Card className="p-6">
                        <h2 className="mb-4 text-lg font-semibold">
                            Ações Rápidas
                        </h2>
                        <div className="grid gap-3">
                            <Link href="/employees">
                                <Button
                                    variant="outline"
                                    className="w-full justify-start border-primary/20 transition-all duration-200 hover:bg-primary hover:text-primary-foreground"
                                >
                                    <Users className="mr-2 size-4" />
                                    Ver Funcionários
                                </Button>
                            </Link>
                            <Link href="/attendances">
                                <Button
                                    variant="outline"
                                    className="w-full justify-start border-primary/20 transition-all duration-200 hover:bg-primary hover:text-primary-foreground"
                                >
                                    <UserCheck className="mr-2 size-4" />
                                    Ver Presenças
                                </Button>
                            </Link>
                            <Link href="/reports">
                                <Button
                                    variant="outline"
                                    className="w-full justify-start border-primary/20 transition-all duration-200 hover:bg-primary hover:text-primary-foreground"
                                >
                                    <FileText className="mr-2 size-4" />
                                    Ver Relatórios
                                </Button>
                            </Link>
                        </div>
                    </Card>
                </div>

                <Card className="p-6">
                    <h2 className="mb-4 text-lg font-semibold">
                        Estatísticas do Mês
                    </h2>
                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="rounded-lg border border-primary/10 bg-card p-4 shadow-sm">
                            <p className="text-sm text-muted-foreground">
                                Taxa de Presença
                            </p>
                            <p className="mt-2 text-2xl font-bold text-secondary">
                                {stats.activeEmployees > 0
                                    ? Math.round(
                                          (stats.presentToday /
                                              stats.activeEmployees) *
                                              100,
                                      )
                                    : 0}
                                %
                            </p>
                        </div>
                        <div className="rounded-lg border border-primary/10 bg-card p-4 shadow-sm">
                            <p className="text-sm text-muted-foreground">
                                Média de Horas por Funcionário
                            </p>
                            <p className="mt-2 text-2xl font-bold text-primary">
                                {stats.activeEmployees > 0
                                    ? (
                                          Number(stats.totalHoursThisMonth) /
                                          stats.activeEmployees
                                      ).toFixed(1)
                                    : 0}
                                h
                            </p>
                        </div>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
