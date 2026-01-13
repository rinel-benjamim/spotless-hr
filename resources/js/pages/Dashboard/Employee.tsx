import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type Attendance, type BreadcrumbItem, type Employee } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Calendar, Clock, LogIn, LogOut, User } from 'lucide-react';

interface EmployeeDashboardProps {
    employee?: Employee;
    lastAttendance?: Attendance;
    todayAttendances: Attendance[];
    monthAttendances: Attendance[];
    message?: string;
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

const formatTime = (dateTime: string) => {
    return new Date(dateTime).toLocaleTimeString('pt-PT', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getEmployeeRoleLabel = (role: string) => {
    const roles: Record<string, string> = {
        manager: 'Gerente',
        supervisor: 'Supervisor',
        operator: 'Operador',
        washer: 'Lavador',
        ironer: 'Passador',
        delivery_driver: 'Motorista de Entrega',
        customer_service: 'Atendimento ao Cliente',
    };
    return roles[role] || role;
};

const getContractTypeLabel = (type: string) => {
    const types: Record<string, string> = {
        full_time: 'Tempo Integral',
        part_time: 'Meio Período',
        temporary: 'Temporário',
        internship: 'Estágio',
    };
    return types[type] || type;
};

const handleCheckIn = () => {
    router.post('/attendances/check-in', {});
};

const handleCheckOut = () => {
    router.post('/attendances/check-out', {});
};

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
                <Head title="Dashboard - Funcionário" />
                <div className="flex min-h-[400px] items-center justify-center p-6">
                    <Card className="p-8 text-center">
                        <p className="text-muted-foreground">
                            {message || 'Perfil de funcionário não encontrado.'}
                        </p>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    const canCheckIn =
        !lastAttendance || lastAttendance.type === 'check_out';
    const canCheckOut =
        lastAttendance && lastAttendance.type === 'check_in';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard - Funcionário" />

            <div className="flex flex-col gap-6 p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <Card className="p-6">
                        <div className="mb-4 flex items-center gap-2">
                            <User className="size-5 text-primary" />
                            <h2 className="text-lg font-semibold">
                                Informações Pessoais
                            </h2>
                        </div>
                        <div className="space-y-2">
                            <div>
                                <span className="text-sm text-muted-foreground">
                                    Nome:
                                </span>
                                <p className="font-medium">
                                    {employee.full_name}
                                </p>
                            </div>
                            <div>
                                <span className="text-sm text-muted-foreground">
                                    Código:
                                </span>
                                <p className="font-medium">
                                    {employee.employee_code}
                                </p>
                            </div>
                            <div>
                                <span className="text-sm text-muted-foreground">
                                    Função:
                                </span>
                                <p className="font-medium">
                                    {getEmployeeRoleLabel(employee.role)}
                                </p>
                            </div>
                            <div>
                                <span className="text-sm text-muted-foreground">
                                    Tipo de Contrato:
                                </span>
                                <p className="font-medium">
                                    {getContractTypeLabel(
                                        employee.contract_type,
                                    )}
                                </p>
                            </div>
                            {employee.shift && (
                                <div>
                                    <span className="text-sm text-muted-foreground">
                                        Turno:
                                    </span>
                                    <p className="font-medium">
                                        {employee.shift.name} (
                                        {employee.shift.start_time} -{' '}
                                        {employee.shift.end_time})
                                    </p>
                                </div>
                            )}
                        </div>
                    </Card>

                    <Card className="p-6">
                        <div className="mb-4 flex items-center gap-2">
                            <Clock className="size-5 text-primary" />
                            <h2 className="text-lg font-semibold">
                                Registo de Ponto
                            </h2>
                        </div>
                        <div className="space-y-4">
                            {lastAttendance && (
                                <div>
                                    <span className="text-sm text-muted-foreground">
                                        Última Marcação:
                                    </span>
                                    <p className="font-medium">
                                        {lastAttendance.type === 'check_in'
                                            ? 'Entrada'
                                            : 'Saída'}{' '}
                                        às{' '}
                                        {formatTime(lastAttendance.recorded_at)}
                                    </p>
                                </div>
                            )}
                            <div className="flex gap-2">
                                <Button
                                    onClick={handleCheckIn}
                                    disabled={!canCheckIn}
                                    className="flex-1"
                                    variant={canCheckIn ? 'default' : 'outline'}
                                >
                                    <LogIn className="mr-2 size-4" />
                                    Marcar Entrada
                                </Button>
                                <Button
                                    onClick={handleCheckOut}
                                    disabled={!canCheckOut}
                                    className="flex-1"
                                    variant={
                                        canCheckOut ? 'default' : 'outline'
                                    }
                                >
                                    <LogOut className="mr-2 size-4" />
                                    Marcar Saída
                                </Button>
                            </div>
                        </div>
                    </Card>
                </div>

                <Card className="p-6">
                    <div className="mb-4 flex items-center gap-2">
                        <Calendar className="size-5 text-primary" />
                        <h2 className="text-lg font-semibold">
                            Presenças de Hoje
                        </h2>
                    </div>
                    {todayAttendances.length > 0 ? (
                        <div className="space-y-2">
                            {todayAttendances.map((attendance) => (
                                <div
                                    key={attendance.id}
                                    className="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div className="flex items-center gap-2">
                                        {attendance.type === 'check_in' ? (
                                            <LogIn className="size-4 text-green-600" />
                                        ) : (
                                            <LogOut className="size-4 text-red-600" />
                                        )}
                                        <span className="font-medium">
                                            {attendance.type === 'check_in'
                                                ? 'Entrada'
                                                : 'Saída'}
                                        </span>
                                    </div>
                                    <span className="text-sm text-muted-foreground">
                                        {formatTime(attendance.recorded_at)}
                                    </span>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-center text-sm text-muted-foreground">
                            Nenhuma presença registada hoje
                        </p>
                    )}
                </Card>

                <Card className="p-6">
                    <h2 className="mb-4 text-lg font-semibold">
                        Histórico Mensal
                    </h2>
                    {monthAttendances.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
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
                                    {monthAttendances.map((attendance) => (
                                        <tr
                                            key={attendance.id}
                                            className="border-b last:border-0"
                                        >
                                            <td className="py-3 text-sm">
                                                {attendance.type === 'check_in'
                                                    ? 'Entrada'
                                                    : 'Saída'}
                                            </td>
                                            <td className="py-3 text-sm text-muted-foreground">
                                                {formatDateTime(
                                                    attendance.recorded_at,
                                                )}
                                            </td>
                                            <td className="py-3 text-sm text-muted-foreground">
                                                {attendance.notes || '-'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <p className="text-center text-sm text-muted-foreground">
                            Nenhuma presença registada este mês
                        </p>
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
