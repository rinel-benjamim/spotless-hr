import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Payroll } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { ArrowLeft, CheckCircle, RefreshCcw } from 'lucide-react';

interface PayrollsShowProps {
    payroll: Payroll;
}

export default function PayrollsShow({ payroll }: PayrollsShowProps) {
    const { post: postMarkPaid, processing: processingPaid } = useForm();
    const { post: postRecalculate, processing: processingRecalculate } = useForm();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Folhas de Pagamento', href: '/payrolls' },
        { title: 'Detalhes', href: '#' },
    ];

    const referenceMonth = format(new Date(payroll.reference_month), 'MMMM yyyy', {
        locale: ptBR,
    });

    const handleMarkAsPaid = () => {
        if (confirm('Marcar esta folha como paga?')) {
            postMarkPaid(`/payrolls/${payroll.id}/mark-paid`);
        }
    };

    const handleRecalculate = () => {
        postRecalculate(`/payrolls/${payroll.id}/recalculate`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Folha de Pagamento - ${payroll.employee?.full_name}`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/payrolls">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="size-4" />
                            </Button>
                        </Link>
                        <h1 className="text-2xl font-bold">
                            Folha de Pagamento - {payroll.employee?.full_name}
                        </h1>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            onClick={handleRecalculate}
                            disabled={processingRecalculate || !!payroll.paid_at}
                        >
                            <RefreshCcw className={`mr-2 size-4 ${processingRecalculate ? 'animate-spin' : ''}`} />
                            Recalcular
                        </Button>
                        {!payroll.paid_at && (
                            <Button onClick={handleMarkAsPaid} disabled={processingPaid}>
                                <CheckCircle className="mr-2 size-4" />
                                Marcar como Paga
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <Card className="p-6">
                        <h2 className="mb-4 text-sm font-medium text-muted-foreground uppercase tracking-wider">
                            Informações Gerais
                        </h2>
                        <div className="space-y-3">
                            <div>
                                <span className="text-sm text-muted-foreground">Funcionário</span>
                                <p className="font-medium">{payroll.employee?.full_name}</p>
                                <p className="text-xs text-muted-foreground">{payroll.employee?.employee_code}</p>
                            </div>
                            <div>
                                <span className="text-sm text-muted-foreground">Mês de Referência</span>
                                <p className="font-medium capitalize">{referenceMonth}</p>
                            </div>
                            <div>
                                <span className="text-sm text-muted-foreground">Estado</span>
                                <div className="mt-1">
                                    {payroll.paid_at ? (
                                        <span className="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">
                                            Pago em {format(new Date(payroll.paid_at), 'dd/MM/yyyy')}
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center rounded-full bg-yellow-50 px-2.5 py-0.5 text-xs font-medium text-yellow-700 ring-1 ring-yellow-600/20 ring-inset">
                                            Pendente
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6 md:col-span-2">
                        <h2 className="mb-4 text-sm font-medium text-muted-foreground uppercase tracking-wider">
                            Resumo de Presenças
                        </h2>
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div className="rounded-lg bg-muted/50 p-3">
                                <span className="text-xs text-muted-foreground">Dias Trabalhados</span>
                                <p className="text-xl font-bold">{payroll.total_days_worked}</p>
                            </div>
                            <div className="rounded-lg bg-muted/50 p-3">
                                <span className="text-xs text-muted-foreground">Faltas</span>
                                <p className="text-xl font-bold text-red-600">{payroll.absences_count}</p>
                            </div>
                            <div className="rounded-lg bg-muted/50 p-3">
                                <span className="text-xs text-muted-foreground">Atrasos</span>
                                <p className="text-xl font-bold text-orange-600">{payroll.late_count}</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6 md:col-span-3">
                        <h2 className="mb-6 text-sm font-medium text-muted-foreground uppercase tracking-wider">
                            Detalhamento Financeiro
                        </h2>
                        <div className="space-y-4">
                            <div className="flex justify-between border-b pb-2">
                                <span className="text-muted-foreground">Salário Base</span>
                                <span className="font-medium">€{Number(payroll.base_salary).toFixed(2)}</span>
                            </div>
                            <div className="flex justify-between border-b pb-2">
                                <span className="text-muted-foreground">Bónus / Extras</span>
                                <span className="font-medium text-green-600">+ €{Number(payroll.total_bonus).toFixed(2)}</span>
                            </div>
                            <div className="flex justify-between border-b pb-2 text-red-600">
                                <span>Descontos (Faltas/Atrasos)</span>
                                <span className="font-medium">- €{Number(payroll.total_deductions).toFixed(2)}</span>
                            </div>
                            <div className="flex justify-between pt-4 text-xl font-bold">
                                <span>Salário Líquido</span>
                                <span className="text-green-600">€{Number(payroll.net_salary).toFixed(2)}</span>
                            </div>
                        </div>

                        {payroll.notes && (
                            <div className="mt-8 rounded-lg border p-4 bg-muted/30">
                                <span className="text-xs font-semibold text-muted-foreground uppercase">Observações</span>
                                <p className="mt-1 text-sm">{payroll.notes}</p>
                            </div>
                        )}
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
