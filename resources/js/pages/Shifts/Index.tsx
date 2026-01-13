import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PaginatedData, type Shift } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    Clock,
    Pencil,
    Plus,
    Trash2,
    Users,
} from 'lucide-react';

interface ShiftsIndexProps {
    shifts: PaginatedData<Shift>;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Turnos',
        href: '/shifts',
    },
];

const handleDelete = (id: number) => {
    if (confirm('Tem certeza que deseja remover este turno?')) {
        router.delete(`/shifts/${id}`);
    }
};

export default function ShiftsIndex({ shifts }: ShiftsIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Turnos" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Turnos</h1>
                    <Link href="/shifts/create">
                        <Button>
                            <Plus className="mr-2 size-4" />
                            Novo Turno
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {shifts.data.length > 0 ? (
                        shifts.data.map((shift) => (
                            <Card key={shift.id} className="p-6">
                                <div className="mb-4 flex items-start justify-between">
                                    <div>
                                        <h3 className="text-lg font-semibold">
                                            {shift.name}
                                        </h3>
                                        <div className="mt-1 flex items-center gap-1 text-sm text-muted-foreground">
                                            <Clock className="size-3" />
                                            {shift.start_time} -{' '}
                                            {shift.end_time}
                                        </div>
                                    </div>
                                    <div className="flex gap-1">
                                        <Link href={`/shifts/${shift.id}/edit`}>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                            >
                                                <Pencil className="size-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() =>
                                                handleDelete(shift.id)
                                            }
                                        >
                                            <Trash2 className="size-4 text-destructive" />
                                        </Button>
                                    </div>
                                </div>

                                {shift.description && (
                                    <p className="mb-3 text-sm text-muted-foreground">
                                        {shift.description}
                                    </p>
                                )}

                                <div className="flex items-center gap-1 text-sm">
                                    <Users className="size-4 text-muted-foreground" />
                                    <span className="font-medium">
                                        {shift.employees_count || 0}
                                    </span>
                                    <span className="text-muted-foreground">
                                        funcionário(s)
                                    </span>
                                </div>
                            </Card>
                        ))
                    ) : (
                        <Card className="col-span-full p-12">
                            <p className="text-center text-sm text-muted-foreground">
                                Nenhum turno encontrado
                            </p>
                        </Card>
                    )}
                </div>

                {shifts.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <div className="text-sm text-muted-foreground">
                            Mostrando {shifts.from} a {shifts.to} de{' '}
                            {shifts.total} resultados
                        </div>
                        <div className="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                disabled={shifts.current_page === 1}
                                onClick={() =>
                                    router.get(
                                        `/shifts?page=${shifts.current_page - 1}`,
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
                                    shifts.current_page === shifts.last_page
                                }
                                onClick={() =>
                                    router.get(
                                        `/shifts?page=${shifts.current_page + 1}`,
                                    )
                                }
                            >
                                Próximo
                                <ChevronRight className="size-4" />
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
