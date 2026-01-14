import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Employee, type Schedule } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { format, getDaysInMonth, startOfMonth } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { ChevronLeft, ChevronRight, FileText, Plus } from 'lucide-react';

interface SchedulesIndexProps {
    schedules: Record<number, Schedule[]>;
    employees: Employee[];
    year: number;
    month: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Escalas', href: '/schedules' },
];

export default function SchedulesIndex({
    schedules,
    employees,
    year,
    month,
}: SchedulesIndexProps) {
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
        router.get(`/schedules?year=${newYear}&month=${newMonth}`);
    };

    const daysInMonth = getDaysInMonth(new Date(year, month - 1));
    const firstDay = startOfMonth(new Date(year, month - 1));
    const days = Array.from({ length: daysInMonth }, (_, i) => i + 1);

    const getScheduleForDay = (
        employeeId: number,
        day: number,
    ): Schedule | undefined => {
        const employeeSchedules = schedules[employeeId] || [];
        return employeeSchedules.find((s) => {
            const scheduleDate = new Date(s.date);
            return scheduleDate.getDate() === day;
        });
    };

    const getDayClass = (schedule: Schedule | undefined, day: number) => {
        const date = new Date(year, month - 1, day);
        const isWeekend = date.getDay() === 0 || date.getDay() === 6;

        if (!schedule) {
            return isWeekend ? 'bg-gray-100' : 'bg-white';
        }

        if (schedule.is_working_day) {
            return 'bg-green-50 text-green-700';
        }

        return 'bg-gray-100 text-gray-500';
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Escalas" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Escalas de Trabalho</h1>
                    <div className="flex gap-2">
                        <a href={`/schedules/export-pdf?year=${year}&month=${month}`} target="_blank">
                            <Button variant="outline">
                                <FileText className="mr-2 size-4" />
                                Exportar Escala Geral (PDF)
                            </Button>
                        </a>
                        <Link href="/schedules/create">
                            <Button>
                                <Plus className="mr-2 size-4" />
                                Criar Escala
                            </Button>
                        </Link>
                    </div>
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

                {employees.length === 0 ? (
                    <Card className="p-12">
                        <div className="text-center text-muted-foreground">
                            Nenhum funcionário ativo encontrado.
                        </div>
                    </Card>
                ) : (
                    <div className="space-y-6">
                        {employees.map((employee) => (
                            <Card key={employee.id} className="p-4">
                                <div className="mb-4 flex items-center justify-between">
                                    <div>
                                        <h3 className="text-lg font-semibold">
                                            {employee.full_name}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            {employee.employee_code} -{' '}
                                            {employee.shift?.name}
                                        </p>
                                    </div>
                                    <a href={`/schedules/export-pdf?year=${year}&month=${month}&employee_id=${employee.id}`} target="_blank">
                                        <Button variant="ghost" size="sm">
                                            <FileText className="mr-2 size-4" />
                                            Exportar PDF
                                        </Button>
                                    </a>
                                </div>

                                <div className="overflow-x-auto">
                                    <div className="grid min-w-max grid-cols-31 gap-1">
                                        {days.map((day) => {
                                            const schedule = getScheduleForDay(
                                                employee.id,
                                                day,
                                            );
                                            const date = new Date(
                                                year,
                                                month - 1,
                                                day,
                                            );
                                            const dayName = format(
                                                date,
                                                'EEE',
                                                {
                                                    locale: ptBR,
                                                },
                                            );

                                            return (
                                                <div
                                                    key={day}
                                                    className={`flex min-h-16 flex-col items-center justify-center rounded border p-2 text-center text-xs ${getDayClass(schedule, day)}`}
                                                >
                                                    <div className="font-semibold">
                                                        {day}
                                                    </div>
                                                    <div className="text-[10px] uppercase">
                                                        {dayName}
                                                    </div>
                                                    {schedule?.shift && (
                                                        <div className="mt-1 text-[10px]">
                                                            {schedule.shift.start_time.slice(
                                                                0,
                                                                5,
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </Card>
                        ))}
                    </div>
                )}

                <Card className="p-4">
                    <h3 className="mb-2 font-semibold">Legenda:</h3>
                    <div className="flex flex-wrap gap-4 text-sm">
                        <div className="flex items-center gap-2">
                            <div className="size-4 rounded border bg-green-50"></div>
                            <span>Dia de Trabalho</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="size-4 rounded border bg-gray-100"></div>
                            <span>Folga / Fim de Semana</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="size-4 rounded border bg-white"></div>
                            <span>Sem Escala</span>
                        </div>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
