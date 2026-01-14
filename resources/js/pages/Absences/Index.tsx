import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Employee } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { Calendar, Filter, UserCheck } from 'lucide-react';
import { useState } from 'react';

interface Absence {
    employee: {
        id: number;
        full_name: string;
        employee_code: string;
    };
    date: string;
    type: string;
}

interface AbsencesIndexProps {
    absences: Absence[];
    filters: {
        start_date: string;
        end_date: string;
    };
    employees: Employee[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Faltas',
        href: '/absences',
    },
];

export default function AbsencesIndex({
    absences,
    filters,
    employees,
}: AbsencesIndexProps) {
    const [startDate, setStartDate] = useState(filters.start_date);
    const [endDate, setEndDate] = useState(filters.end_date);

    const handleFilter = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            '/absences',
            { start_date: startDate, end_date: endDate },
            { preserveState: true },
        );
    };

    const formatDate = (dateString: string) => {
        return format(parseISO(dateString), 'dd/MM/yyyy', { locale: ptBR });
    };

    const getDayName = (dateString: string) => {
        return format(parseISO(dateString), 'EEEE', { locale: ptBR });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Faltas" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Gestão de Faltas</h1>
                    <Link href="/justifications">
                        <Button variant="outline">
                            Ver Justificativas
                        </Button>
                    </Link>
                </div>

                <Card className="p-4">
                    <form
                        onSubmit={handleFilter}
                        className="flex flex-wrap items-end gap-4"
                    >
                        <div className="grid w-full max-w-sm items-center gap-1.5">
                            <Label htmlFor="start_date">Data Inicial</Label>
                            <Input
                                type="date"
                                id="start_date"
                                value={startDate}
                                onChange={(e) => setStartDate(e.target.value)}
                            />
                        </div>
                        <div className="grid w-full max-w-sm items-center gap-1.5">
                            <Label htmlFor="end_date">Data Final</Label>
                            <Input
                                type="date"
                                id="end_date"
                                value={endDate}
                                onChange={(e) => setEndDate(e.target.value)}
                            />
                        </div>
                        <Button type="submit">
                            <Filter className="mr-2 size-4" />
                            Filtrar
                        </Button>
                    </form>
                </Card>

                <Card className="overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-muted/50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Funcionário
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Data
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Dia da Semana
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {absences.map((absence, index) => (
                                    <tr
                                        key={`${absence.employee.id}-${absence.date}-${index}`}
                                        className="hover:bg-muted/50"
                                    >
                                        <td className="px-6 py-4 text-sm font-medium">
                                            {absence.employee.full_name}
                                            <div className="text-xs text-muted-foreground">
                                                {absence.employee.employee_code}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            {formatDate(absence.date)}
                                        </td>
                                        <td className="px-6 py-4 text-sm capitalize">
                                            {getDayName(absence.date)}
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            <span
                                                className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                    absence.type === 'justified'
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-red-100 text-red-800'
                                                }`}
                                            >
                                                {absence.type === 'justified'
                                                    ? 'Justificada'
                                                    : 'Falta'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            {absence.type !== 'justified' && (
                                                <Link
                                                    href={`/justifications/create?employee_id=${absence.employee.id}&absence_date=${absence.date}`}
                                                >
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                    >
                                                        <UserCheck className="mr-2 size-4" />
                                                        Justificar
                                                    </Button>
                                                </Link>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                                {absences.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="px-6 py-12 text-center text-sm text-muted-foreground"
                                        >
                                            Nenhuma falta encontrada no período selecionado.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
