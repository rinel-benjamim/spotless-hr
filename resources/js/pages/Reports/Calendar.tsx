import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type Attendance, type BreadcrumbItem, type Employee } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { format, startOfMonth, endOfMonth, eachDayOfInterval, isSameMonth, isWeekend } from 'date-fns';
import { ptBR } from 'date-fns/locale';

interface CalendarProps {
    employee: Employee;
    attendances: Record<string, Attendance[]>;
    absences: Array<{ date: string; type: string }>;
    year: number;
    month: number;
}

export default function Calendar({
    employee,
    attendances,
    absences,
    year,
    month,
}: CalendarProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
            title: 'Relatórios',
            href: '/reports',
        },
        {
            title: employee.full_name,
            href: `/reports/employee/${employee.id}`,
        },
        {
            title: 'Calendário',
            href: `/reports/calendar/${employee.id}`,
        },
    ];

    const currentDate = new Date(year, month - 1, 1);
    const monthName = format(currentDate, 'MMMM yyyy', { locale: ptBR });
    
    const monthStart = startOfMonth(currentDate);
    const monthEnd = endOfMonth(currentDate);
    const daysInMonth = eachDayOfInterval({ start: monthStart, end: monthEnd });

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

        router.get(`/reports/calendar/${employee.id}?year=${newYear}&month=${newMonth}`);
    };

    const getDayStatus = (day: Date) => {
        const dateStr = format(day, 'yyyy-MM-dd');
        
        if (!isSameMonth(day, currentDate)) {
            return { status: 'other-month', label: '', color: 'bg-gray-50' };
        }
        
        if (isWeekend(day)) {
            return { status: 'weekend', label: 'Fim de Semana', color: 'bg-gray-100' };
        }

        const absence = absences.find((a) => a.date === dateStr);
        if (absence) {
            if (absence.type === 'justified') {
                return { status: 'justified', label: 'Justificada', color: 'bg-green-100 border-green-300' };
            }
            return { status: 'absence', label: 'Falta', color: 'bg-red-100 border-red-300' };
        }

        if (attendances[dateStr]?.length > 0) {
            const checkIn = attendances[dateStr].find((a) => a.type === 'check_in');
            const checkOut = attendances[dateStr].find((a) => a.type === 'check_out');
            
            return {
                status: 'present',
                label: checkIn && checkOut ? 'Completo' : 'Parcial',
                color: 'bg-blue-50 border-blue-300',
                checkIn: checkIn ? format(new Date(checkIn.recorded_at), 'HH:mm') : null,
                checkOut: checkOut ? format(new Date(checkOut.recorded_at), 'HH:mm') : null,
            };
        }

        return { status: 'no-data', label: '', color: '' };
    };

    const weekDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

    const startDay = monthStart.getDay();
    const emptyDays = Array.from({ length: startDay }, (_, i) => i);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Calendário - ${employee.full_name}`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">{employee.full_name}</h1>
                        <p className="text-sm text-muted-foreground">
                            {employee.employee_code} • {employee.shift?.name}
                        </p>
                    </div>
                </div>

                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold capitalize">{monthName}</h2>
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

                <div className="flex gap-4">
                    <div className="flex items-center gap-2">
                        <div className="size-4 rounded border-2 border-blue-300 bg-blue-50"></div>
                        <span className="text-sm">Presente</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="size-4 rounded border-2 border-red-300 bg-red-100"></div>
                        <span className="text-sm">Falta</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="size-4 rounded border-2 border-green-300 bg-green-100"></div>
                        <span className="text-sm">Justificada</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="size-4 rounded bg-gray-100"></div>
                        <span className="text-sm">Fim de Semana</span>
                    </div>
                </div>

                <Card className="overflow-hidden p-6">
                    <div className="grid grid-cols-7 gap-2">
                        {weekDays.map((day) => (
                            <div
                                key={day}
                                className="py-2 text-center text-sm font-semibold text-muted-foreground"
                            >
                                {day}
                            </div>
                        ))}
                        
                        {emptyDays.map((i) => (
                            <div key={`empty-${i}`} className="min-h-[100px]"></div>
                        ))}
                        
                        {daysInMonth.map((day) => {
                            const dayStatus = getDayStatus(day);
                            return (
                                <div
                                    key={day.toISOString()}
                                    className={`min-h-[100px] rounded-lg border-2 p-2 ${dayStatus.color}`}
                                >
                                    <div className="text-sm font-semibold">
                                        {format(day, 'd')}
                                    </div>
                                    {dayStatus.label && (
                                        <div className="mt-1 text-xs font-medium">
                                            {dayStatus.label}
                                        </div>
                                    )}
                                    {dayStatus.checkIn && (
                                        <div className="mt-2 text-xs">
                                            <div className="text-green-700">↓ {dayStatus.checkIn}</div>
                                        </div>
                                    )}
                                    {dayStatus.checkOut && (
                                        <div className="text-xs">
                                            <div className="text-blue-700">↑ {dayStatus.checkOut}</div>
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
