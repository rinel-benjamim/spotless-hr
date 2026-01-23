import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type Attendance, type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { ArrowRight, Clock, FileText, TrendingUp, UserCheck, Users } from 'lucide-react';

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
    { title: 'Dashboard', href: '/dashboard' },
];

export default function AdminDashboard({
    stats,
    recentAttendances,
}: AdminDashboardProps) {
    const statCards = [
        {
            title: 'Total de Funcionários',
            value: stats.totalEmployees,
            icon: Users,
            color: 'text-primary',
            bgColor: 'bg-primary/10',
        },
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
            <Badge variant="default" className="bg-primary text-primary-foreground">
                Entrada
            </Badge>
        ) : (
            <Badge variant="secondary" className="bg-secondary text-secondary-foreground">
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
                        <h1 className="text-2xl font-bold">
                            Dashboard Administrativo
                        </h1>
                        <p className="text-muted-foreground">
                            Visão geral do sistema de gestão de RH
                        </p>
                    </div>
                    <a href="/dashboard/export-kpis" target="_blank">
                        <Button variant="outline">
                            <FileText className="mr-2 size-4" />
                            Exportar KPIs
                        </Button>
                    </a>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
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
                                {recentAttendances.map((attendance) => (
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
                            <Link href="/employees/create">
                                <Button
                                    variant="outline"
                                    className="w-full justify-start border-primary/20 hover:bg-primary hover:text-primary-foreground transition-all duration-200"
                                >
                                    <Users className="mr-2 size-4" />
                                    Adicionar Funcionário
                                </Button>
                            </Link>
                            <Link href="/payrolls/create">
                                <Button
                                    variant="outline"
                                    className="w-full justify-start border-primary/20 hover:bg-primary hover:text-primary-foreground transition-all duration-200"
                                >
                                    <Clock className="mr-2 size-4" />
                                    Gerar Folha de Pagamento
                                </Button>
                            </Link>
                            <Link href="/schedules/create">
                                <Button
                                    variant="outline"
                                    className="w-full justify-start border-primary/20 hover:bg-primary hover:text-primary-foreground transition-all duration-200"
                                >
                                    <TrendingUp className="mr-2 size-4" />
                                    Criar Escala
                                </Button>
                            </Link>
                            <Link href="/reports">
                                <Button
                                    variant="outline"
                                    className="w-full justify-start border-primary/20 hover:bg-primary hover:text-primary-foreground transition-all duration-200"
                                >
                                    <UserCheck className="mr-2 size-4" />
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
                    <div className="grid gap-4 md:grid-cols-3">
                        <div className="rounded-lg border border-primary/10 bg-card p-4 shadow-sm">
                            <p className="text-sm text-muted-foreground">
                                Média de Presenças Diárias
                            </p>
                            <p className="mt-2 text-2xl font-bold text-primary">
                                {Math.round(stats.presentToday * 0.9)}
                            </p>
                        </div>
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
