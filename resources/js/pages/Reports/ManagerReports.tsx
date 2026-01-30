import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Report } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { FileText, Plus } from 'lucide-react';
import { useState } from 'react';

interface ManagerReportsProps {
    reports: {
        data: Report[];
        links: any[];
        meta: any;
    };
}

export default function ManagerReports({ reports }: ManagerReportsProps) {
    const [showCreateForm, setShowCreateForm] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        description: '',
        type: '',
        data: {},
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Relatórios', href: '/reports' },
        { title: 'Meus Relatórios', href: '/reports/manager' },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('reports.create'), {
            onSuccess: () => {
                reset();
                setShowCreateForm(false);
            },
        });
    };

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
            <Head title="Meus Relatórios" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Meus Relatórios</h1>
                        <p className="text-sm text-muted-foreground">
                            Gerencie seus relatórios para envio ao administrador
                        </p>
                    </div>
                    <Button onClick={() => setShowCreateForm(true)}>
                        <Plus className="mr-2 size-4" />
                        Novo Relatório
                    </Button>
                </div>

                {showCreateForm && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Criar Novo Relatório</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div>
                                    <Label htmlFor="title">Título</Label>
                                    <Input
                                        id="title"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        error={errors.title}
                                        required
                                    />
                                </div>

                                <div>
                                    <Label htmlFor="type">Tipo</Label>
                                    <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione o tipo" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="attendance">Presenças</SelectItem>
                                            <SelectItem value="payroll">Folha de Pagamento</SelectItem>
                                            <SelectItem value="schedule">Horários</SelectItem>
                                            <SelectItem value="general">Geral</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.type && <p className="text-sm text-red-600">{errors.type}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="description">Descrição</Label>
                                    <Textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        rows={3}
                                    />
                                </div>

                                <div className="flex gap-2">
                                    <Button type="submit" disabled={processing}>
                                        Criar Relatório
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setShowCreateForm(false)}
                                    >
                                        Cancelar
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}

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
                                        <p className="text-xs text-muted-foreground">
                                            Criado em {format(new Date(report.created_at), 'dd/MM/yyyy HH:mm', { locale: ptBR })}
                                        </p>
                                    </div>
                                    <div className="flex gap-2">
                                        {report.file_path && (
                                            <Button variant="outline" size="sm">
                                                <FileText className="mr-2 size-4" />
                                                Download
                                            </Button>
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