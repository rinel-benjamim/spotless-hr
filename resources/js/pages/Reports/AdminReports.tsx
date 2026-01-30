import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Report } from '@/types';
import { Head } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { FileText, User } from 'lucide-react';

interface AdminReportsProps {
    reports: {
        data: Report[];
        links: any[];
        meta: any;
    };
}

export default function AdminReports({ reports }: AdminReportsProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Relatórios', href: '/reports' },
        { title: 'Relatórios dos Gestores', href: '/reports/admin' },
    ];

    const getStatusBadge = (status: string) => {
        const styles = {
            pending: 'bg-yellow-100 text-yellow-800',
            completed: 'bg-green-100 text-green-800',
            failed: 'bg-red-100 text-red-800',
        };
        return styles[status as keyof typeof styles] || styles.pending;
    };

    const getTypeBadge = (type: string) => {
        const types = {
            attendance: 'Presenças',
            payroll: 'Folha de Pagamento',
            schedule: 'Horários',
            general: 'Geral',
        };
        return types[type as keyof typeof types] || type;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Relatórios dos Gestores" />

            <div className="flex flex-col gap-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold">Relatórios dos Gestores</h1>
                    <p className="text-sm text-muted-foreground">
                        Visualize todos os relatórios criados pelos gestores
                    </p>
                </div>

                <div className="grid gap-4">
                    {reports.data.map((report) => (
                        <Card key={report.id}>
                            <CardContent className="p-6">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2 mb-2">
                                            <h3 className="font-semibold">{report.title}</h3>
                                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusBadge(report.status)}`}>
                                                {report.status === 'pending' && 'Pendente'}
                                                {report.status === 'completed' && 'Concluído'}
                                                {report.status === 'failed' && 'Falhou'}
                                            </span>
                                            <span className="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {getTypeBadge(report.type)}
                                            </span>
                                        </div>
                                        {report.description && (
                                            <p className="text-sm text-muted-foreground mb-2">
                                                {report.description}
                                            </p>
                                        )}
                                        <div className="flex items-center gap-4 text-xs text-muted-foreground">
                                            <div className="flex items-center gap-1">
                                                <User className="size-3" />
                                                {report.creator?.name}
                                            </div>
                                            <div>
                                                Criado em {format(new Date(report.created_at), 'dd/MM/yyyy HH:mm', { locale: ptBR })}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex gap-2">
                                        {report.file_path && (
                                            <button className="flex items-center gap-2 px-3 py-1 text-sm border rounded-md hover:bg-gray-50">
                                                <FileText className="size-4" />
                                                Download
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}

                    {reports.data.length === 0 && (
                        <Card>
                            <CardContent className="p-6 text-center">
                                <p className="text-muted-foreground">Nenhum relatório encontrado.</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}