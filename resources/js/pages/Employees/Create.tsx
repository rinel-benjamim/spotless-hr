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
    type EmployeeRole,
    type EmployeeStatus,
    type Shift,
} from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface EmployeesCreateProps {
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
        title: 'Criar',
        href: '/employees/create',
    },
];

export default function EmployeesCreate({ shifts }: EmployeesCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        user_id: '',
        full_name: '',
        role: '',
        contract_type: '',
        shift_id: '',
        base_salary: '',
        deduction_per_absence: '',
        email: '',
        password: '',
        status: 'active',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/employees');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Criar Funcionário" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Link href="/employees">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="size-4" />
                        </Button>
                    </Link>
                    <h1 className="text-2xl font-bold">Criar Funcionário</h1>
                </div>

                <Card className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="full_name">
                                    Nome Completo *
                                </Label>
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
                                        setData('role', value as EmployeeRole)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione uma função" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="admin">
                                            Diretor Geral
                                        </SelectItem>
                                        <SelectItem value="manager">
                                            Gerente
                                        </SelectItem>
                                        <SelectItem value="employee">
                                            Funcionário
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

                            <div className="space-y-2">
                                <Label htmlFor="base_salary">
                                    Salário Base (Kz)
                                </Label>
                                <Input
                                    id="base_salary"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={data.base_salary}
                                    onChange={(e) =>
                                        setData('base_salary', e.target.value)
                                    }
                                    placeholder="0.00"
                                />
                                <InputError message={errors.base_salary} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="deduction_per_absence">
                                    Dedução por Falta (Kz)
                                </Label>
                                <Input
                                    id="deduction_per_absence"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={data.deduction_per_absence}
                                    onChange={(e) =>
                                        setData(
                                            'deduction_per_absence',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="0.00"
                                />
                                <InputError
                                    message={errors.deduction_per_absence}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                    placeholder="joao@exemplo.com"
                                />
                                <InputError message={errors.email} />
                                <p className="text-sm text-muted-foreground">
                                    Opcional. Se não fornecido, será gerado
                                    automaticamente se a senha for preenchida.
                                </p>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="password">
                                    Senha (para criar conta de utilizador)
                                </Label>
                                <Input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                    placeholder="Mínimo 8 caracteres"
                                />
                                <InputError message={errors.password} />
                                <p className="text-sm text-muted-foreground">
                                    Se fornecido, será criada automaticamente
                                    uma conta de utilizador para este
                                    funcionário.
                                </p>
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
                                Criar Funcionário
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
