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
    type Attendance,
    type BreadcrumbItem,
    type Employee,
    type PaginatedData,
} from '@/types';
import { Head, router } from '@inertiajs/react';
import { Calendar, ChevronLeft, ChevronRight, FileText, Filter } from 'lucide-react';
import { useState } from 'react';

interface AttendancesIndexProps {
    attendances: PaginatedData<Attendance>;
    employees: Employee[];
    filters: {
        employee_id?: string;
        start_date?: string;
        end_date?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Presenças',
        href: '/attendances',
    },
];

const formatDateTime = (dateTime: string) => {
    return new Date(dateTime).toLocaleString('pt-PT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getAttendanceTypeBadge = (type: string) => {
    return type === 'check_in' ? (
        <span className="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">
            Entrada
        </span>
    ) : (
        <span className="inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-red-600/20 ring-inset">
            Saída
        </span>
    );
};

export default function AttendancesIndex({
    attendances,
    employees,
    filters,
}: AttendancesIndexProps) {
    const [employeeId, setEmployeeId] = useState(filters.employee_id ?? 'all');
    const [startDate, setStartDate] = useState(filters.start_date || '');
    const [endDate, setEndDate] = useState(filters.end_date || '');

    const handleFilter = (e: React.FormEvent) => {
        e.preventDefault();

        const params: Record<string, string | undefined> = {
            start_date: startDate || undefined,
            end_date: endDate || undefined,
        };

        if (employeeId && employeeId !== 'all') {
            params.employee_id = employeeId;
        }

        router.get('/attendances', params);
    };

    const handleClearFilters = () => {
        setEmployeeId('all');
        setStartDate('');
        setEndDate('');
        router.get('/attendances');
    };

    const getExportUrl = (type: 'pdf' | 'excel') => {
        const params = new URLSearchParams();
        if (employeeId && employeeId !== 'all') params.append('employee_id', employeeId);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        return `/attendances/export-${type}?${params.toString()}`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Presenças" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Presenças</h1>
                    <div className="flex gap-2">
                        <a href={getExportUrl('pdf')} target="_blank">
                            <Button variant="outline">
                                <FileText className="mr-2 size-4" />
                                Exportar PDF
                            </Button>
                        </a>
                    </div>
                </div>

                <Card className="p-6">
                    <form
                        onSubmit={handleFilter}
                        className="grid gap-4 md:grid-cols-4"
                    >
                        <div className="space-y-2">
                            <Label htmlFor="employee_id">Funcionário</Label>
                            <Select
                                value={employeeId}
                                onValueChange={setEmployeeId}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Todos</SelectItem>
                                    {employees.map((employee) => (
                                        <SelectItem
                                            key={employee.id}
                                            value={employee.id.toString()}
                                        >
                                            {employee.full_name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="start_date">Data Inicial</Label>
                            <Input
                                id="start_date"
                                type="date"
                                value={startDate}
                                onChange={(e) => setStartDate(e.target.value)}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="end_date">Data Final</Label>
                            <Input
                                id="end_date"
                                type="date"
                                value={endDate}
                                onChange={(e) => setEndDate(e.target.value)}
                            />
                        </div>

                        <div className="flex items-end gap-2">
                            <Button type="submit" className="flex-1">
                                <Filter className="mr-2 size-4" />
                                Filtrar
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleClearFilters}
                            >
                                Limpar
                            </Button>
                        </div>
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
                                        Turno
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Tipo
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Data/Hora
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Notas
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {attendances.data.length > 0 ? (
                                    attendances.data.map((attendance) => (
                                        <tr key={attendance.id}>
                                            <td className="px-6 py-4 text-sm font-medium">
                                                {attendance.employee
                                                    ?.full_name || 'N/A'}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {attendance.employee?.shift
                                                    ?.name || '-'}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {getAttendanceTypeBadge(
                                                    attendance.type,
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <div className="flex items-center gap-1 text-muted-foreground">
                                                    <Calendar className="size-3" />
                                                    {formatDateTime(
                                                        attendance.recorded_at,
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-muted-foreground">
                                                {attendance.notes || '-'}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="px-6 py-12 text-center text-sm text-muted-foreground"
                                        >
                                            Nenhuma presença encontrada
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {attendances.last_page > 1 && (
                        <div className="flex items-center justify-between border-t px-6 py-4">
                            <div className="text-sm text-muted-foreground">
                                Mostrando {attendances.from} a {attendances.to}{' '}
                                de {attendances.total} resultados
                            </div>
                            <div className="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    disabled={attendances.current_page === 1}
                                    onClick={() =>
                                        router.get(
                                            `/attendances?page=${attendances.current_page - 1}&employee_id=${employeeId}&start_date=${startDate}&end_date=${endDate}`,
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
                                        attendances.current_page ===
                                        attendances.last_page
                                    }
                                    onClick={() =>
                                        router.get(
                                            `/attendances?page=${attendances.current_page + 1}&employee_id=${employeeId}&start_date=${startDate}&end_date=${endDate}`,
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
