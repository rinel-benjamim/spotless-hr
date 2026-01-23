import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import {
    type BreadcrumbItem,
    type Employee,
    type PaginatedData,
} from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    Eye,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-react';

interface EmployeesIndexProps {
    employees: PaginatedData<Employee>;
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
];

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

const handleDelete = (id: number) => {
    if (confirm('Tem certeza que deseja remover este funcionário?')) {
        router.delete(`/employees/${id}`);
    }
};

export default function EmployeesIndex({ employees }: EmployeesIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Funcionários" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Funcionários</h1>
                    <Link href="/employees/create">
                        <Button>
                            <Plus className="mr-2 size-4" />
                            Novo Funcionário
                        </Button>
                    </Link>
                </div>

                <Card className="overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-muted/50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Código
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Nome
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Função
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Turno
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Estado
                                    </th>
                                    <th className="px-6 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {employees.data.length > 0 ? (
                                    employees.data.map((employee) => (
                                        <tr key={employee.id}>
                                            <td className="px-6 py-4 text-sm">
                                                {employee.employee_code}
                                            </td>
                                            <td className="px-6 py-4 text-sm font-medium">
                                                {employee.full_name}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {getEmployeeRoleLabel(
                                                    employee.role,
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {employee.shift?.name || '-'}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {getStatusBadge(
                                                    employee.status,
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-right text-sm">
                                                <div className="flex justify-end gap-2">
                                                    <Link
                                                        href={`/employees/${employee.id}`}
                                                    >
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                        >
                                                            <Eye className="size-4" />
                                                        </Button>
                                                    </Link>
                                                    <Link
                                                        href={`/employees/${employee.id}/edit`}
                                                    >
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                        >
                                                            <Pencil className="size-4" />
                                                        </Button>
                                                    </Link>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() =>
                                                            handleDelete(
                                                                employee.id,
                                                            )
                                                        }
                                                    >
                                                        <Trash2 className="size-4 text-destructive" />
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan={6}
                                            className="px-6 py-12 text-center text-sm text-muted-foreground"
                                        >
                                            Nenhum funcionário encontrado
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {employees.last_page > 1 && (
                        <div className="flex items-center justify-between border-t px-6 py-4">
                            <div className="text-sm text-muted-foreground">
                                Mostrando {employees.from} a {employees.to} de{' '}
                                {employees.total} resultados
                            </div>
                            <div className="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    disabled={employees.current_page === 1}
                                    onClick={() =>
                                        router.get(
                                            `/employees?page=${employees.current_page - 1}`,
                                        )
                                    }
                                >
                                    <ChevronLeft className="size-4" />
                                    Anterior
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    disabled={
                                        employees.current_page ===
                                        employees.last_page
                                    }
                                    onClick={() =>
                                        router.get(
                                            `/employees?page=${employees.current_page + 1}`,
                                        )
                                    }
                                >
                                    Próximo
                                    <ChevronRight className="size-4" />
                                </Button>
                            </div>
                        </div>
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
