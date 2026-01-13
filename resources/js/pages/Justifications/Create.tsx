import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Employee } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface JustificationsCreateProps {
    employees: Employee[];
    selectedEmployee?: Employee;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Justificativas', href: '/justifications' },
    { title: 'Criar', href: '/justifications/create' },
];

export default function JustificationsCreate({ employees, selectedEmployee }: JustificationsCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        employee_id: selectedEmployee?.id?.toString() || '',
        absence_date: '',
        reason: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/justifications');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Criar Justificativa" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Link href="/justifications">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="size-4" />
                        </Button>
                    </Link>
                    <h1 className="text-2xl font-bold">Criar Justificativa</h1>
                </div>

                <Card className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="employee_id">Funcionário *</Label>
                            <Select
                                value={data.employee_id}
                                onValueChange={(value) => setData('employee_id', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecione um funcionário" />
                                </SelectTrigger>
                                <SelectContent>
                                    {employees.map((employee) => (
                                        <SelectItem key={employee.id} value={employee.id.toString()}>
                                            {employee.full_name} ({employee.employee_code})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.employee_id} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="absence_date">Data da Ausência</Label>
                            <Input
                                type="date"
                                id="absence_date"
                                value={data.absence_date}
                                onChange={(e) => setData('absence_date', e.target.value)}
                            />
                            <InputError message={errors.absence_date} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="reason">Motivo *</Label>
                            <Textarea
                                id="reason"
                                value={data.reason}
                                onChange={(e) => setData('reason', e.target.value)}
                                rows={4}
                                placeholder="Descreva o motivo da justificativa..."
                            />
                            <InputError message={errors.reason} />
                        </div>

                        <div className="flex justify-end gap-4">
                            <Link href="/justifications">
                                <Button type="button" variant="outline">
                                    Cancelar
                                </Button>
                            </Link>
                            <Button type="submit" disabled={processing}>
                                <Save className="mr-2 size-4" />
                                Salvar Justificativa
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
