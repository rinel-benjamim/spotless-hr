import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Employee } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Calendar, Pencil } from 'lucide-react';

interface EmployeesShowProps {
    employee: Employee;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Funcionários',
        href: '/employees',
    },
    {
        title: 'Detalhes',
        href: '#',
    },
];

const getEmployeeRoleLabel = (role: string) => {
    const roles: Record<string, string> = {
        admin: 'Diretor Geral',
        manager: 'Gerente',
        employee: 'Funcionário',
        washer: 'Lavador',
        ironer: 'Passador',
        attendant: 'Atendente',
        driver: 'Motorista',
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

const getStatusBadge = (status: string) => {
    return status === 'active' ? (
        <span className="inline-flex items-center rounded-full bg-primary/10 px-2 py-1 text-xs font-medium text-primary ring-1 ring-primary/20 ring-inset">
            Ativo
        </span>
    ) : (
        <span className="inline-flex items-center rounded-full bg-muted px-2 py-1 text-xs font-medium text-muted-foreground ring-1 ring-border ring-inset">
            Inativo
        </span>
    );
};

const formatDateTime = (dateTime: string) => {
    return new Date(dateTime).toLocaleString('pt-PT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

export default function EmployeesShow({ employee }: EmployeesShowProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={employee.full_name} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/employees">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="size-4" />
                            </Button>
                        </Link>
                        <h1 className="text-2xl font-bold">
                            {employee.full_name}
                        </h1>
                    </div>
                    <Link href={`/employees/${employee.id}/edit`}>
                        <Button>
                            <Pencil className="mr-2 size-4" />
                            Editar
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card className="p-6">
                        <h2 className="mb-4 text-lg font-semibold">
                            Informações Gerais
                        </h2>
                        <div className="space-y-3">
                            <div>
                                <span className="text-sm text-muted-foreground">
                                    Código
                                </span>
                                <p className="font-medium">
                                    {employee.employee_code}
                                </p>
                            </div>
                            <div>
                                <span className="text-sm text-muted-foreground">
                                    Nome Completo
                                </span>
                                <p className="font-medium">
                                    {employee.full_name}
                                </p>
                            </div>
                            <div>
                                <span className="text-sm text-muted-foreground">
                                    Função
                                </span>
                                <p className="font-medium">
                                    {getEmployeeRoleLabel(employee.role)}
                                </p>
                            </div>
                            <div>
                                <span className="text-sm text-muted-foreground">
                                    Tipo de Contrato
                                </span>
                                <p className="font-medium">
                                    {getContractTypeLabel(
                                        employee.contract_type,
                                    )}
                                </p>
                            </div>
                            <div>
                                <span className="text-sm text-muted-foreground">
                                    Estado
                                </span>
                                <div className="mt-1">
                                    {getStatusBadge(employee.status)}
                                </div>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6">
                        <h2 className="mb-4 text-lg font-semibold">Turno</h2>
                        {employee.shift ? (
                            <div className="space-y-3">
                                <div>
                                    <span className="text-sm text-muted-foreground">
                                        Nome
                                    </span>
                                    <p className="font-medium">
                                        {employee.shift.name}
                                    </p>
                                </div>
                                <div>
                                    <span className="text-sm text-muted-foreground">
                                        Horário
                                    </span>
                                    <p className="font-medium">
                                        {employee.shift.start_time} -{' '}
                                        {employee.shift.end_time}
                                    </p>
                                </div>
                                {employee.shift.description && (
                                    <div>
                                        <span className="text-sm text-muted-foreground">
                                            Descrição
                                        </span>
                                        <p className="font-medium">
                                            {employee.shift.description}
                                        </p>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Nenhum turno atribuído
                            </p>
                        )}
                    </Card>
                </div>

                <Card className="p-6">
                    <div className="mb-4 flex items-center gap-2">
                        <Calendar className="size-5 text-primary" />
                        <h2 className="text-lg font-semibold">
                            Presenças Recentes
                        </h2>
                    </div>
                    {employee.attendances && employee.attendances.length > 0 ? (
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
                                    {employee.attendances.map((attendance) => (
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
                            Nenhuma presença registada
                        </p>
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
