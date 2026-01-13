import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Turnos',
        href: '/shifts',
    },
    {
        title: 'Criar',
        href: '/shifts/create',
    },
];

export default function ShiftsCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        start_time: '',
        end_time: '',
        description: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/shifts');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Criar Turno" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Link href="/shifts">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="size-4" />
                        </Button>
                    </Link>
                    <h1 className="text-2xl font-bold">Criar Turno</h1>
                </div>

                <Card className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="name">Nome do Turno *</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    placeholder="Turno da Manhã"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Descrição</Label>
                                <Input
                                    id="description"
                                    value={data.description}
                                    onChange={(e) =>
                                        setData('description', e.target.value)
                                    }
                                    placeholder="Descrição do turno"
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="start_time">
                                    Hora de Início *
                                </Label>
                                <Input
                                    id="start_time"
                                    type="time"
                                    value={data.start_time}
                                    onChange={(e) =>
                                        setData('start_time', e.target.value)
                                    }
                                />
                                <InputError message={errors.start_time} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="end_time">Hora de Fim *</Label>
                                <Input
                                    id="end_time"
                                    type="time"
                                    value={data.end_time}
                                    onChange={(e) =>
                                        setData('end_time', e.target.value)
                                    }
                                />
                                <InputError message={errors.end_time} />
                            </div>
                        </div>

                        <div className="flex justify-end gap-4">
                            <Link href="/shifts">
                                <Button type="button" variant="outline">
                                    Cancelar
                                </Button>
                            </Link>
                            <Button type="submit" disabled={processing}>
                                <Save className="mr-2 size-4" />
                                Criar Turno
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
