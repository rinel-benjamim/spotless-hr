import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Employee } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface PayrollsCreateProps {
    employees: Employee[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Folhas de Pagamento', href: '/payrolls' },
    { title: 'Gerar', href: '/payrolls/create' },
];

export default function PayrollsCreate({ employees }: PayrollsCreateProps) {
    const currentDate = new Date();
    const { data, setData, post, processing, errors } = useForm({
        employee_id: '',
        year: currentDate.getFullYear(),
        month: currentDate.getMonth() + 1,
        generate_all: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/payrolls');
    };

    const months = [
        { value: 1, label: 'Janeiro' },
        { value: 2, label: 'Fevereiro' },
        { value: 3, label: 'Março' },
        { value: 4, label: 'Abril' },
        { value: 5, label: 'Maio' },
        { value: 6, label: 'Junho' },
        { value: 7, label: 'Julho' },
        { value: 8, label: 'Agosto' },
        { value: 9, label: 'Setembro' },
        { value: 10, label: 'Outubro' },
        { value: 11, label: 'Novembro' },
        { value: 12, label: 'Dezembro' },
    ];

    const years = Array.from(
        { length: 5 },
        (_, i) => currentDate.getFullYear() - 2 + i,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Gerar Folha de Pagamento" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Link href="/payrolls">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="size-4" />
                        </Button>
                    </Link>
                    <h1 className="text-2xl font-bold">
                        Gerar Folha de Pagamento
                    </h1>
                </div>

                <Card className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="year">Ano *</Label>
                                <Select
                                    value={data.year.toString()}
                                    onValueChange={(value) =>
                                        setData('year', parseInt(value))
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {years.map((year) => (
                                            <SelectItem
                                                key={year}
                                                value={year.toString()}
                                            >
                                                {year}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.year} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="month">Mês *</Label>
                                <Select
                                    value={data.month.toString()}
                                    onValueChange={(value) =>
                                        setData('month', parseInt(value))
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {months.map((month) => (
                                            <SelectItem
                                                key={month.value}
                                                value={month.value.toString()}
                                            >
                                                {month.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.month} />
                            </div>
                        </div>

                        <div className="space-y-4 rounded-lg border p-4">
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="generate_all"
                                    checked={data.generate_all}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'generate_all',
                                            checked as boolean,
                                        )
                                    }
                                />
                                <Label
                                    htmlFor="generate_all"
                                    className="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    Gerar para todos os funcionários ativos
                                </Label>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Ao marcar esta opção, as folhas de pagamento
                                serão geradas automaticamente para todos os
                                funcionários ativos com salário base definido.
                            </p>
                        </div>

                        {!data.generate_all && (
                            <div className="space-y-2">
                                <Label htmlFor="employee_id">
                                    Funcionário *
                                </Label>
                                <Select
                                    value={data.employee_id}
                                    onValueChange={(value) =>
                                        setData('employee_id', value)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione um funcionário" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {employees.map((employee) => (
                                            <SelectItem
                                                key={employee.id}
                                                value={employee.id.toString()}
                                            >
                                                {employee.employee_code} -{' '}
                                                {employee.full_name} (
                                                Kz {Number(
                                                    employee.base_salary,
                                                ).toFixed(2)})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.employee_id} />
                            </div>
                        )}

                        <div className="flex justify-end gap-4">
                            <Link href="/payrolls">
                                <Button type="button" variant="outline">
                                    Cancelar
                                </Button>
                            </Link>
                            <Button type="submit" disabled={processing}>
                                <Save className="mr-2 size-4" />
                                Gerar Folha
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
