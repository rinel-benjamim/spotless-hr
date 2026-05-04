import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Employee } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { Filter, UserCheck } from 'lucide-react';
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
    pendingJustificationsCount?: number;
    isAdmin?: boolean;
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
    pendingJustificationsCount = 0,
    isAdmin = false,
}: AbsencesIndexProps) {
    const [startDate, setStartDate] = useState(filters.start_date);
    const [endDate, setEndDate] = useState(filters.end_date);
    const [selectedAbsence, setSelectedAbsence] = useState<Absence | null>(
        null,
    );

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
                            {pendingJustificationsCount > 0 && (
                                <Badge className="ml-2 bg-orange-500 text-white">
                                    {pendingJustificationsCount}
                                </Badge>
                            )}
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
                                                    ? 'Falta Justificada'
                                                    : 'Falta'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            {absence.type === 'justified' ? (
                                                <span className="text-sm text-muted-foreground">
                                                    -
                                                </span>
                                            ) : isAdmin ? (
                                                <Dialog>
                                                    <DialogTrigger asChild>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() =>
                                                                setSelectedAbsence(
                                                                    absence,
                                                                )
                                                            }
                                                        >
                                                            <UserCheck className="mr-2 size-4" />
                                                            Ver Detalhes
                                                        </Button>
                                                    </DialogTrigger>
                                                    <DialogContent>
                                                        <DialogHeader>
                                                            <DialogTitle>
                                                                Detalhes da
                                                                Falta
                                                            </DialogTitle>
                                                            <DialogDescription>
                                                                Informações
                                                                sobre a falta do
                                                                funcionário
                                                            </DialogDescription>
                                                        </DialogHeader>
                                                        <div className="space-y-4 py-4">
                                                            <div>
                                                                <Label className="text-muted-foreground">
                                                                    Funcionário
                                                                </Label>
                                                                <p className="font-medium">
                                                                    {
                                                                        selectedAbsence
                                                                            ?.employee
                                                                            .full_name
                                                                    }
                                                                </p>
                                                                <p className="text-sm text-muted-foreground">
                                                                    {
                                                                        selectedAbsence
                                                                            ?.employee
                                                                            .employee_code
                                                                    }
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <Label className="text-muted-foreground">
                                                                    Data
                                                                </Label>
                                                                <p className="font-medium">
                                                                    {selectedAbsence &&
                                                                        formatDate(
                                                                            selectedAbsence.date,
                                                                        )}
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <Label className="text-muted-foreground">
                                                                    Dia da
                                                                    Semana
                                                                </Label>
                                                                <p className="font-medium capitalize">
                                                                    {selectedAbsence &&
                                                                        getDayName(
                                                                            selectedAbsence.date,
                                                                        )}
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <Label className="text-muted-foreground">
                                                                    Status
                                                                </Label>
                                                                <p className="font-medium">
                                                                    {selectedAbsence?.type ===
                                                                    'justified'
                                                                        ? 'Falta Justificada'
                                                                        : 'Falta'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </DialogContent>
                                                </Dialog>
                                            ) : (
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
                                            Nenhuma falta encontrada no período
                                            selecionado.
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
