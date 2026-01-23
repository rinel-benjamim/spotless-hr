import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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
import { type BreadcrumbItem, type Employee, type Shift } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface SchedulesCreateProps {
    employees: Employee[];
    shifts: Shift[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Escalas', href: '/schedules' },
    { title: 'Criar', href: '/schedules/create' },
];

export default function SchedulesCreate({
    employees,
    shifts,
}: SchedulesCreateProps) {
    const currentDate = new Date();
    const { data, setData, post, processing, errors } = useForm({
        employee_ids: [] as string[],
        date: '',
        shift_id: '',
        is_working_day: true,
        notes: '',
        generate_month: false,
        year: currentDate.getFullYear(),
        month: currentDate.getMonth() + 1,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/schedules');
    };

    const toggleEmployee = (id: string) => {
        const currentIds = [...data.employee_ids];
        const index = currentIds.indexOf(id);
        if (index === -1) {
            currentIds.push(id);
        } else {
            currentIds.splice(index, 1);
        }
        setData('employee_ids', currentIds);
    };

    const toggleAllEmployees = () => {
        if (data.employee_ids.length === employees.length) {
            setData('employee_ids', []);
        } else {
            setData('employee_ids', employees.map(e => e.id.toString()));
        }
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
            <Head title="Criar Escala" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Link href="/schedules">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="size-4" />
                        </Button>
                    </Link>
                    <h1 className="text-2xl font-bold">Criar Escala</h1>
                </div>

                <Card className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <Label>Funcionários *</Label>
                                <Button
                                    type="button"
                                    variant="link"
                                    size="sm"
                                    onClick={toggleAllEmployees}
                                >
                                    {data.employee_ids.length === employees.length
                                        ? 'Desmarcar Todos'
                                        : 'Selecionar Todos'}
                                </Button>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 border rounded-md p-4 max-h-60 overflow-y-auto">
                                {employees.map((employee) => (
                                    <div key={employee.id} className="flex items-center space-x-2">
                                        <Checkbox
                                            id={`employee-${employee.id}`}
                                            checked={data.employee_ids.includes(employee.id.toString())}
                                            onCheckedChange={() => toggleEmployee(employee.id.toString())}
                                        />
                                        <Label
                                            htmlFor={`employee-${employee.id}`}
                                            className="text-sm font-normal cursor-pointer"
                                        >
                                            {employee.employee_code} - {employee.full_name}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                            <InputError message={errors.employee_ids} />
                        </div>

                        <div className="space-y-4 rounded-lg border p-4">
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="generate_month"
                                    checked={data.generate_month}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'generate_month',
                                            checked as boolean,
                                        )
                                    }
                                />
                                <Label
                                    htmlFor="generate_month"
                                    className="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    Gerar escala para o mês completo
                                </Label>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Ao marcar esta opção, será gerada
                                automaticamente uma escala para todos os dias do
                                mês selecionado, com dias úteis marcados como
                                dias de trabalho.
                            </p>
                        </div>

                        {data.generate_month ? (
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
                        ) : (
                            <div className="space-y-2">
                                <Label htmlFor="date">Data *</Label>
                                <Input
                                    id="date"
                                    type="date"
                                    value={data.date}
                                    onChange={(e) =>
                                        setData('date', e.target.value)
                                    }
                                />
                                <InputError message={errors.date} />
                            </div>
                        )}

                        <div className="space-y-2">
                            <Label htmlFor="shift_id">Turno</Label>
                            <Select
                                value={data.shift_id}
                                onValueChange={(value) =>
                                    setData('shift_id', value)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione um turno (ou usar turno padrão)" />
                                </SelectTrigger>
                                <SelectContent>
                                    {shifts.map((shift) => (
                                        <SelectItem
                                            key={shift.id}
                                            value={shift.id.toString()}
                                        >
                                            {shift.name} ({shift.start_time} -{' '}
                                            {shift.end_time})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.shift_id} />
                            <p className="text-sm text-muted-foreground">
                                Se não selecionado, será usado o turno padrão do
                                funcionário.
                            </p>
                        </div>

                        {!data.generate_month && (
                            <>
                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_working_day"
                                        checked={data.is_working_day}
                                        onCheckedChange={(checked) =>
                                            setData(
                                                'is_working_day',
                                                checked as boolean,
                                            )
                                        }
                                    />
                                    <Label
                                        htmlFor="is_working_day"
                                        className="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Dia de trabalho
                                    </Label>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="notes">Observações</Label>
                                    <Input
                                        id="notes"
                                        value={data.notes}
                                        onChange={(e) =>
                                            setData('notes', e.target.value)
                                        }
                                        placeholder="Observações adicionais..."
                                    />
                                    <InputError message={errors.notes} />
                                </div>
                            </>
                        )}

                        <div className="flex justify-end gap-4">
                            <Link href="/schedules">
                                <Button type="button" variant="outline">
                                    Cancelar
                                </Button>
                            </Link>
                            <Button type="submit" disabled={processing}>
                                <Save className="mr-2 size-4" />
                                Criar Escala
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
