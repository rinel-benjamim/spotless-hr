import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Employee } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { BarChart3, Calendar, FileText } from 'lucide-react';

interface ReportsIndexProps {
    employees: Employee[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Relatórios',
        href: '/reports',
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

export default function ReportsIndex({ employees }: ReportsIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Relatórios" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Relatórios de Assiduidade</h1>
                </div>

                <div className="grid gap-6">
                    {employees.map((employee) => (
                        <Card key={employee.id} className="p-6">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <h3 className="text-lg font-semibold">{employee.full_name}</h3>
                                    <p className="text-sm text-muted-foreground">
                                        {employee.employee_code} • {getEmployeeRoleLabel(employee.role)}
                                    </p>
                                    {employee.shift && (
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            Turno: {employee.shift.name} ({employee.shift.start_time} - {employee.shift.end_time})
                                        </p>
                                    )}
                                </div>

                                <div className="flex gap-2">
                                    <Link href={`/reports/employee/${employee.id}`}>
                                        <Button variant="outline" size="sm">
                                            <FileText className="mr-2 size-4" />
                                            Relatório Mensal
                                        </Button>
                                    </Link>
                                    <Link href={`/reports/calendar/${employee.id}`}>
                                        <Button variant="outline" size="sm">
                                            <Calendar className="mr-2 size-4" />
                                            Calendário
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </Card>
                    ))}

                    {employees.length === 0 && (
                        <Card className="p-12">
                            <div className="flex flex-col items-center justify-center text-center">
                                <BarChart3 className="size-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-semibold">Nenhum funcionário encontrado</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Adicione funcionários para visualizar relatórios.
                                </p>
                            </div>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
