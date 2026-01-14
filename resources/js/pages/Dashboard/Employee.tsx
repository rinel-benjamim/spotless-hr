import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type Attendance, type BreadcrumbItem, type Employee } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import {
    Calendar,
    Clock,
    FileText,
    LogIn,
    LogOut,
    TrendingUp,
} from 'lucide-react';

interface EmployeeDashboardProps {
    employee?: Employee;
    lastAttendance?: Attendance;
    todayAttendances: Attendance[];
    monthAttendances: Attendance[];
    message?: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

export default function EmployeeDashboard({
    employee,
    lastAttendance,
    todayAttendances,
    monthAttendances,
    message,
}: EmployeeDashboardProps) {
    if (message || !employee) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Dashboard" />
                <div className="flex h-full items-center justify-center p-6">
                    <Card className="p-12">
                        <div className="text-center">
                            <p className="text-lg text-muted-foreground">
                                {message ||
                                    'Perfil de funcionário não encontrado.'}
                            </p>
                        </div>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    const canCheckIn = !lastAttendance || lastAttendance.type === 'check_out';
    const canCheckOut = lastAttendance && lastAttendance.type === 'check_in';

    const handleCheckIn = () => {
        router.post('/attendances/check-in', {
            employee_id: employee.id,
        });
    };

    const handleCheckOut = () => {
        router.post('/attendances/check-out', {
            employee_id: employee.id,
        });
    };

    const todayCheckIns = todayAttendances.filter(
        (a) => a.type === 'check_in',
    ).length;
    const todayCheckOuts = todayAttendances.filter(
        (a) => a.type === 'check_out',
    ).length;

    const monthCheckIns = monthAttendances.filter(
        (a) => a.type === 'check_in',
    ).length;

    const getAttendanceTypeBadge = (type: string) => {
        return type === 'check_in' ? (
            <Badge variant="default" className="bg-green-600">
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
                <div>
                    <h1 className="text-2xl font-bold">
                        Bem-vindo, {employee.full_name}!
                    </h1>
                    <p className="text-muted-foreground">
                        {employee.employee_code} - {employee.shift?.name}
                    </p>
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">
                                    Estado
                                </p>
                                <p className="mt-2 text-2xl font-bold">
                                    {canCheckOut ? 'Presente' : 'Ausente'}
                                </p>
                            </div>
                            <div
                                className={`rounded-lg p-3 ${canCheckOut ? 'bg-green-50' : 'bg-gray-50'}`}
                            >
                                <Clock
                                    className={`size-6 ${canCheckOut ? 'text-green-600' : 'text-gray-600'}`}
                                />
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">
                                    Presenças este Mês
                                </p>
                                <p className="mt-2 text-2xl font-bold">
                                    {monthCheckIns}
                                </p>
                            </div>
                            <div className="rounded-lg bg-blue-50 p-3">
                                <Calendar className="size-6 text-blue-600" />
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">
                                    Registos Hoje
                                </p>
                                <p className="mt-2 text-2xl font-bold">
                                    {todayAttendances.length}
                                </p>
                            </div>
                            <div className="rounded-lg bg-purple-50 p-3">
                                <TrendingUp className="size-6 text-purple-600" />
                            </div>
                        </div>
                    </Card>
                </div>

                <Card className="p-6">
                    <h2 className="mb-4 text-lg font-semibold">
                        Marcar Presença
                    </h2>
                    <div className="flex flex-wrap gap-4">
                        <Button
                            onClick={handleCheckIn}
                            disabled={!canCheckIn}
                            size="lg"
                            className="bg-green-600 hover:bg-green-700"
                        >
                            <LogIn className="mr-2 size-5" />
                            Marcar Entrada
                        </Button>
                        <Button
                            onClick={handleCheckOut}
                            disabled={!canCheckOut}
                            size="lg"
                            variant="secondary"
                        >
                            <LogOut className="mr-2 size-5" />
                            Marcar Saída
                        </Button>
                    </div>
                    {lastAttendance && (
                        <p className="mt-4 text-sm text-muted-foreground">
                            Último registo:{' '}
                            {lastAttendance.type === 'check_in'
                                ? 'Entrada'
                                : 'Saída'}{' '}
                            em{' '}
                            {format(
                                new Date(lastAttendance.recorded_at),
                                "dd/MM/yyyy 'às' HH:mm",
                                { locale: ptBR },
                            )}
                        </p>
                    )}
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card className="p-6">
                        <h2 className="mb-4 text-lg font-semibold">
                            Registos de Hoje
                        </h2>
                        {todayAttendances.length === 0 ? (
                            <div className="py-8 text-center text-sm text-muted-foreground">
                                Nenhum registo hoje
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {todayAttendances.map((attendance) => (
                                    <div
                                        key={attendance.id}
                                        className="flex items-center justify-between rounded-lg border p-3"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {format(
                                                    new Date(
                                                        attendance.recorded_at,
                                                    ),
                                                    'HH:mm',
                                                    { locale: ptBR },
                                                )}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {format(
                                                    new Date(
                                                        attendance.recorded_at,
                                                    ),
                                                    'dd/MM/yyyy',
                                                    { locale: ptBR },
                                                )}
                                            </p>
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
                            <Link href={`/reports/employee/${employee.id}`}>
                                <Button
                                    variant="outline"
                                    className="w-full justify-start"
                                >
                                    <FileText className="mr-2 size-4" />
                                    Ver Meus Relatórios
                                </Button>
                            </Link>
                            <Link href={`/reports/calendar/${employee.id}`}>
                                <Button
                                    variant="outline"
                                    className="w-full justify-start"
                                >
                                    <Calendar className="mr-2 size-4" />
                                    Ver Calendário
                                </Button>
                            </Link>
                        </div>
                    </Card>
                </div>

                <Card className="p-6">
                    <h2 className="mb-4 text-lg font-semibold">
                        Informações do Turno
                    </h2>
                    {employee.shift ? (
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="rounded-lg border p-4">
                                <p className="text-sm text-muted-foreground">
                                    Turno
                                </p>
                                <p className="mt-2 text-lg font-semibold">
                                    {employee.shift.name}
                                </p>
                            </div>
                            <div className="rounded-lg border p-4">
                                <p className="text-sm text-muted-foreground">
                                    Horário
                                </p>
                                <p className="mt-2 text-lg font-semibold">
                                    {employee.shift.start_time} -{' '}
                                    {employee.shift.end_time}
                                </p>
                            </div>
                            <div className="rounded-lg border p-4">
                                <p className="text-sm text-muted-foreground">
                                    Tolerância
                                </p>
                                <p className="mt-2 text-lg font-semibold">
                                    {employee.shift.tolerance_minutes} min
                                </p>
                            </div>
                        </div>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            Sem turno atribuído
                        </p>
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
