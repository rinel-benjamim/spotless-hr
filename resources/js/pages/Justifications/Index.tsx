import { Badge } from '@/components/ui/badge';
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
import { Check, ChevronLeft, ChevronRight, Plus, X } from 'lucide-react';

interface JustificationsIndexProps {
    justifications: PaginatedData<Justification>;
    canApprove: boolean;
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

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'pending':
            return (
                <Badge
                    variant="outline"
                    className="border-yellow-300 bg-yellow-50 text-yellow-700"
                >
                    Pendente
                </Badge>
            );
        case 'approved':
            return (
                <Badge variant="default" className="bg-green-600">
                    Aprovada
                </Badge>
            );
        case 'rejected':
            return <Badge variant="destructive">Rejeitada</Badge>;
        default:
            return <Badge>{status}</Badge>;
    }
};

const handleDelete = (id: number) => {
    if (confirm('Tem certeza que deseja remover esta justificativa?')) {
        router.delete(`/justifications/${id}`);
    }
};

const handleApprove = (id: number) => {
    if (confirm('Aprovar esta justificativa?')) {
        router.post(`/justifications/${id}/approve`);
    }
};

const handleReject = (id: number) => {
    if (confirm('Rejeitar esta justificativa?')) {
        router.post(`/justifications/${id}/reject`);
    }
};

export default function JustificationsIndex({
    justifications,
    canApprove,
}: JustificationsIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Justificativas" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Justificativas</h1>
                    {!canApprove && (
                        <Link href="/justifications/create">
                            <Button>
                                <Plus className="mr-2 size-4" />
                                Nova Justificativa
                            </Button>
                        </Link>
                    )}
                </div>

                {canApprove && (
                    <Card className="border-yellow-200 bg-yellow-50 p-4">
                        <p className="text-sm text-yellow-800">
                            As justificativas pendentes aparecem abaixo. Pode
                            aprobar ou rejeitar cada uma.
                        </p>
                    </Card>
                )}

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
                                        Anexo
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Estado
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Criada Em
                                    </th>
                                    {canApprove && (
                                        <th className="px-6 py-3 text-right text-sm font-medium text-muted-foreground">
                                            Ações
                                        </th>
                                    )}
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
                                            {justification.attachment_path ? (
                                                <a
                                                    href={`/storage/${justification.attachment_path}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="text-blue-600 hover:underline"
                                                >
                                                    Ver Anexo
                                                </a>
                                            ) : (
                                                <span className="text-muted-foreground">
                                                    -
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            {getStatusBadge(
                                                justification.status,
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-muted-foreground">
                                            {formatDate(
                                                justification.created_at,
                                            )}
                                        </td>
                                        {canApprove &&
                                            justification.status ===
                                                'pending' && (
                                                <td className="px-6 py-4 text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            className="border-green-300 text-green-700 hover:bg-green-50"
                                                            onClick={() =>
                                                                handleApprove(
                                                                    justification.id,
                                                                )
                                                            }
                                                        >
                                                            <Check className="mr-1 size-4" />
                                                            Aprovar
                                                        </Button>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            className="border-red-300 text-red-700 hover:bg-red-50"
                                                            onClick={() =>
                                                                handleReject(
                                                                    justification.id,
                                                                )
                                                            }
                                                        >
                                                            <X className="mr-1 size-4" />
                                                            Rejeitar
                                                        </Button>
                                                    </div>
                                                </td>
                                            )}
                                        {canApprove &&
                                            justification.status !==
                                                'pending' && (
                                                <td className="px-6 py-4 text-right text-sm text-muted-foreground">
                                                    -
                                                </td>
                                            )}
                                    </tr>
                                ))}
                                {justifications.data.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={canApprove ? 6 : 5}
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
