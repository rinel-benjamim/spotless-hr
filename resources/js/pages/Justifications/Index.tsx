import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import {
    type BreadcrumbItem,
    type Justification,
    type PaginatedData,
} from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { ChevronLeft, ChevronRight, Plus, Trash2 } from 'lucide-react';

interface JustificationsIndexProps {
    justifications: PaginatedData<Justification>;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Justificativas',
        href: '/justifications',
    },
];

const formatDate = (dateString: string) => {
    return format(new Date(dateString), "dd/MM/yyyy 'às' HH:mm", {
        locale: ptBR,
    });
};

const formatAbsenceDate = (dateString: string) => {
    return format(new Date(dateString), 'dd/MM/yyyy', { locale: ptBR });
};

const handleDelete = (id: number) => {
    if (confirm('Tem certeza que deseja remover esta justificativa?')) {
        router.delete(`/justifications/${id}`);
    }
};

export default function JustificationsIndex({
    justifications,
}: JustificationsIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Justificativas" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Justificativas</h1>
                    <Link href="/justifications/create">
                        <Button>
                            <Plus className="mr-2 size-4" />
                            Nova Justificativa
                        </Button>
                    </Link>
                </div>

                <Card className="overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-muted/50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Funcionário
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Data da Ausência
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Motivo
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Justificada Por
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Criada Em
                                    </th>
                                    <th className="px-6 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {justifications.data.map((justification) => (
                                    <tr
                                        key={justification.id}
                                        className="hover:bg-muted/50"
                                    >
                                        <td className="px-6 py-4 text-sm font-medium">
                                            {justification.employee?.full_name}
                                            <div className="text-xs text-muted-foreground">
                                                {
                                                    justification.employee
                                                        ?.employee_code
                                                }
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            {justification.absence_date
                                                ? formatAbsenceDate(
                                                      justification.absence_date,
                                                  )
                                                : '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            <div
                                                className="max-w-md truncate"
                                                title={justification.reason}
                                            >
                                                {justification.reason}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            {justification.justifiedBy?.name ||
                                                '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-muted-foreground">
                                            {formatDate(
                                                justification.created_at,
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() =>
                                                    handleDelete(
                                                        justification.id,
                                                    )
                                                }
                                            >
                                                <Trash2 className="size-4 text-destructive" />
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                                {justifications.data.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={6}
                                            className="px-6 py-12 text-center text-sm text-muted-foreground"
                                        >
                                            Nenhuma justificativa encontrada.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {justifications.last_page > 1 && (
                        <div className="flex items-center justify-between border-t px-6 py-3">
                            <div className="text-sm text-muted-foreground">
                                Mostrando {justifications.from} a{' '}
                                {justifications.to} de {justifications.total}{' '}
                                registros
                            </div>
                            <div className="flex gap-2">
                                <Link
                                    href={`/justifications?page=${justifications.current_page - 1}`}
                                    preserveState
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={
                                            justifications.current_page === 1
                                        }
                                    >
                                        <ChevronLeft className="size-4" />
                                    </Button>
                                </Link>
                                <Link
                                    href={`/justifications?page=${justifications.current_page + 1}`}
                                    preserveState
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={
                                            justifications.current_page ===
                                            justifications.last_page
                                        }
                                    >
                                        <ChevronRight className="size-4" />
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
