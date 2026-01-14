import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import {
    type ActivityLog,
    type BreadcrumbItem,
    type PaginatedData,
} from '@/types';
import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { Activity } from 'lucide-react';

interface ActivityLogsIndexProps {
    logs: PaginatedData<ActivityLog>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Logs de Atividade', href: '/activity-logs' },
];

const getActionBadge = (action: string) => {
    const variants: Record<
        string,
        'default' | 'secondary' | 'destructive' | 'outline'
    > = {
        created: 'default',
        updated: 'secondary',
        deleted: 'destructive',
        login: 'outline',
        logout: 'outline',
    };

    const variant = variants[action.toLowerCase()] || 'outline';

    return (
        <Badge variant={variant} className="capitalize">
            {action}
        </Badge>
    );
};

const getModelTypeLabel = (modelType: string | null) => {
    if (!modelType) return 'Sistema';

    const labels: Record<string, string> = {
        'App\\Models\\Employee': 'Funcionário',
        'App\\Models\\Attendance': 'Assiduidade',
        'App\\Models\\Shift': 'Turno',
        'App\\Models\\Justification': 'Justificação',
        'App\\Models\\Payroll': 'Folha de Pagamento',
        'App\\Models\\Schedule': 'Escala',
        'App\\Models\\User': 'Utilizador',
    };

    return labels[modelType] || modelType;
};

export default function ActivityLogsIndex({ logs }: ActivityLogsIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Logs de Atividade" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">
                            Logs de Atividade
                        </h1>
                        <p className="text-muted-foreground">
                            Histórico de ações realizadas no sistema
                        </p>
                    </div>
                </div>

                {logs.data.length === 0 ? (
                    <Card className="p-12">
                        <div className="flex flex-col items-center gap-2 text-center text-muted-foreground">
                            <Activity className="size-12 opacity-50" />
                            <p>Nenhum log de atividade encontrado.</p>
                        </div>
                    </Card>
                ) : (
                    <>
                        <div className="space-y-4">
                            {logs.data.map((log) => (
                                <Card key={log.id} className="p-4">
                                    <div className="flex items-start justify-between gap-4">
                                        <div className="flex-1 space-y-2">
                                            <div className="flex items-center gap-2">
                                                {getActionBadge(log.action)}
                                                <span className="text-sm text-muted-foreground">
                                                    {getModelTypeLabel(
                                                        log.model_type,
                                                    )}
                                                </span>
                                                {log.model_id && (
                                                    <span className="text-sm text-muted-foreground">
                                                        #{log.model_id}
                                                    </span>
                                                )}
                                            </div>

                                            {log.description && (
                                                <p className="text-sm">
                                                    {log.description}
                                                </p>
                                            )}

                                            <div className="flex items-center gap-4 text-xs text-muted-foreground">
                                                <span>
                                                    {log.user
                                                        ? log.user.name
                                                        : 'Sistema'}
                                                </span>
                                                <span>•</span>
                                                <span>
                                                    {format(
                                                        new Date(
                                                            log.created_at,
                                                        ),
                                                        "dd/MM/yyyy 'às' HH:mm",
                                                        { locale: ptBR },
                                                    )}
                                                </span>
                                                {log.ip_address && (
                                                    <>
                                                        <span>•</span>
                                                        <span>
                                                            {log.ip_address}
                                                        </span>
                                                    </>
                                                )}
                                            </div>

                                            {log.properties &&
                                                Object.keys(log.properties)
                                                    .length > 0 && (
                                                    <details className="mt-2">
                                                        <summary className="cursor-pointer text-xs text-muted-foreground hover:text-foreground">
                                                            Ver propriedades
                                                        </summary>
                                                        <pre className="mt-2 overflow-x-auto rounded bg-muted p-2 text-xs">
                                                            {JSON.stringify(
                                                                log.properties,
                                                                null,
                                                                2,
                                                            )}
                                                        </pre>
                                                    </details>
                                                )}
                                        </div>
                                    </div>
                                </Card>
                            ))}
                        </div>

                        {logs.last_page > 1 && (
                            <div className="flex items-center justify-between">
                                <p className="text-sm text-muted-foreground">
                                    Mostrando {logs.from} a {logs.to} de{' '}
                                    {logs.total} registos
                                </p>
                                <div className="flex gap-2">
                                    {logs.current_page > 1 && (
                                        <Link
                                            href={`/activity-logs?page=${logs.current_page - 1}`}
                                            className="rounded-md border px-3 py-1 text-sm hover:bg-accent"
                                        >
                                            Anterior
                                        </Link>
                                    )}
                                    {logs.current_page < logs.last_page && (
                                        <Link
                                            href={`/activity-logs?page=${logs.current_page + 1}`}
                                            className="rounded-md border px-3 py-1 text-sm hover:bg-accent"
                                        >
                                            Próximo
                                        </Link>
                                    )}
                                </div>
                            </div>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
