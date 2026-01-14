import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PaginatedData, type Payroll } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { ChevronLeft, ChevronRight, DollarSign, Plus } from 'lucide-react';

interface PayrollsIndexProps {
    payrolls: PaginatedData<Payroll>;
    year: number;
    month: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Folhas de Pagamento', href: '/payrolls' },
];

export default function PayrollsIndex({
    payrolls,
    year,
    month,
}: PayrollsIndexProps) {
    const monthName = format(new Date(year, month - 1), 'MMMM yyyy', {
        locale: ptBR,
    });

    const navigateMonth = (direction: number) => {
        let newMonth = month + direction;
        let newYear = year;
        if (newMonth > 12) {
            newMonth = 1;
            newYear++;
        } else if (newMonth < 1) {
            newMonth = 12;
            newYear--;
        }
        router.get(`/payrolls?year=${newYear}&month=${newMonth}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Folhas de Pagamento" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Folhas de Pagamento</h1>
                    <Link href="/payrolls/create">
                        <Button>
                            <Plus className="mr-2 size-4" />
                            Gerar Folha
                        </Button>
                    </Link>
                </div>

                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold capitalize">
                        {monthName}
                    </h2>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => navigateMonth(-1)}
                        >
                            <ChevronLeft className="size-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => navigateMonth(1)}
                        >
                            <ChevronRight className="size-4" />
                        </Button>
                    </div>
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
                                        Salário Base
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Dias Trabalhados
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Faltas
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Descontos
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Salário Líquido
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Estado
                                    </th>
                                    <th className="px-6 py-3 text-right text-sm font-medium text-muted-foreground">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {payrolls.data.map((payroll) => (
                                    <tr
                                        key={payroll.id}
                                        className="hover:bg-muted/50"
                                    >
                                        <td className="px-6 py-4 text-sm font-medium">
                                            {payroll.employee?.full_name}
                                            <div className="text-xs text-muted-foreground">
                                                {
                                                    payroll.employee
                                                        ?.employee_code
                                                }
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            €{Number(payroll.base_salary).toFixed(2)}
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            {payroll.total_days_worked}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-red-600">
                                            {payroll.absences_count}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-red-600">
                                            €
                                            {Number(payroll.total_deductions).toFixed(
                                                2,
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-sm font-semibold text-green-600">
                                            €{Number(payroll.net_salary).toFixed(2)}
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            {payroll.paid_at ? (
                                                <span className="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">
                                                    Pago
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-yellow-600/20 ring-inset">
                                                    Pendente
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Link
                                                href={`/payrolls/${payroll.id}`}
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                >
                                                    <DollarSign className="size-4" />
                                                </Button>
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {payrolls.data.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={8}
                                            className="px-6 py-12 text-center text-sm text-muted-foreground"
                                        >
                                            Nenhuma folha de pagamento
                                            encontrada para este mês.
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
