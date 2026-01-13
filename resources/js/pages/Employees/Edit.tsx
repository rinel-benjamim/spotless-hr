import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import {
    type BreadcrumbItem,
    type ContractType,
    type Employee,
    type EmployeeRole,
    type EmployeeStatus,
    type Shift,
} from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface EmployeesEditProps {
    employee: Employee;
    shifts: Shift[];
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
        title: 'Editar',
        href: '#',
    },
];

export default function EmployeesEdit({
    employee,
    shifts,
}: EmployeesEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        employee_code: employee.employee_code,
        full_name: employee.full_name,
        role: employee.role,
        contract_type: employee.contract_type,
        shift_id: employee.shift_id?.toString() || '',
        status: employee.status,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/employees/${employee.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Editar - ${employee.full_name}`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Link href="/employees">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="size-4" />
                        </Button>
                    </Link>
                    <h1 className="text-2xl font-bold">
                        Editar Funcionário - {employee.full_name}
                    </h1>
                </div>

                <Card className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="employee_code">
                                    Código do Funcionário *
                                </Label>
                                <Input
                                    id="employee_code"
                                    value={data.employee_code}
                                    onChange={(e) =>
                                        setData('employee_code', e.target.value)
                                    }
                                    placeholder="EMP1001"
                                />
                                <InputError message={errors.employee_code} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="full_name">Nome Completo *</Label>
                                <Input
                                    id="full_name"
                                    value={data.full_name}
                                    onChange={(e) =>
                                        setData('full_name', e.target.value)
                                    }
                                    placeholder="João Silva"
                                />
                                <InputError message={errors.full_name} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="role">Função *</Label>
                                <Select
                                    value={data.role}
                                    onValueChange={(value) =>
                                        setData(
                                            'role',
                                            value as EmployeeRole,
                                        )
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione uma função" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="manager">
                                            Gerente
                                        </SelectItem>
                                        <SelectItem value="supervisor">
                                            Supervisor
                                        </SelectItem>
                                        <SelectItem value="operator">
                                            Operador
                                        </SelectItem>
                                        <SelectItem value="washer">
                                            Lavador
                                        </SelectItem>
                                        <SelectItem value="ironer">
                                            Passador
                                        </SelectItem>
                                        <SelectItem value="delivery_driver">
                                            Motorista de Entrega
                                        </SelectItem>
                                        <SelectItem value="customer_service">
                                            Atendimento ao Cliente
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.role} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="contract_type">
                                    Tipo de Contrato *
                                </Label>
                                <Select
                                    value={data.contract_type}
                                    onValueChange={(value) =>
                                        setData(
                                            'contract_type',
                                            value as ContractType,
                                        )
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione um tipo" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="full_time">
                                            Tempo Integral
                                        </SelectItem>
                                        <SelectItem value="part_time">
                                            Meio Período
                                        </SelectItem>
                                        <SelectItem value="temporary">
                                            Temporário
                                        </SelectItem>
                                        <SelectItem value="internship">
                                            Estágio
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.contract_type} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="shift_id">Turno</Label>
                                <Select
                                    value={data.shift_id}
                                    onValueChange={(value) =>
                                        setData('shift_id', value)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione um turno" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {shifts.map((shift) => (
                                            <SelectItem
                                                key={shift.id}
                                                value={shift.id.toString()}
                                            >
                                                {shift.name} ({shift.start_time}{' '}
                                                - {shift.end_time})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.shift_id} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="status">Estado</Label>
                                <Select
                                    value={data.status}
                                    onValueChange={(value) =>
                                        setData(
                                            'status',
                                            value as EmployeeStatus,
                                        )
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">
                                            Ativo
                                        </SelectItem>
                                        <SelectItem value="inactive">
                                            Inativo
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.status} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-4">
                            <Link href="/employees">
                                <Button type="button" variant="outline">
                                    Cancelar
                                </Button>
                            </Link>
                            <Button type="submit" disabled={processing}>
                                <Save className="mr-2 size-4" />
                                Guardar Alterações
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
