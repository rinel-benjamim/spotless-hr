import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type CompanySetting } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';

interface CompanySettingsEditProps {
    settings: CompanySetting;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Configurações da Empresa', href: '/settings/company' },
];

export default function CompanySettingsEdit({
    settings,
}: CompanySettingsEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        company_name: settings.company_name,
        business_hours_start: settings.business_hours_start,
        business_hours_end: settings.business_hours_end,
        timezone: settings.timezone,
        currency: settings.currency,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put('/settings/company');
    };

    const timezones = [
        { value: 'Europe/Lisbon', label: 'Europa/Lisboa' },
        { value: 'Europe/London', label: 'Europa/Londres' },
        { value: 'Europe/Madrid', label: 'Europa/Madrid' },
        { value: 'Europe/Paris', label: 'Europa/Paris' },
        { value: 'Europe/Berlin', label: 'Europa/Berlim' },
        { value: 'America/New_York', label: 'América/Nova Iorque' },
        { value: 'America/Los_Angeles', label: 'América/Los Angeles' },
        { value: 'America/Sao_Paulo', label: 'América/São Paulo' },
    ];

    const currencies = [
        { value: 'EUR', label: 'Euro (€)' },
        { value: 'USD', label: 'Dólar Americano ($)' },
        { value: 'GBP', label: 'Libra Esterlina (£)' },
        { value: 'BRL', label: 'Real Brasileiro (R$)' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Configurações da Empresa" />

            <div className="flex flex-col gap-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold">
                        Configurações da Empresa
                    </h1>
                    <p className="text-muted-foreground">
                        Gerir configurações gerais do sistema
                    </p>
                </div>

                <Card className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="company_name">
                                Nome da Empresa *
                            </Label>
                            <Input
                                id="company_name"
                                value={data.company_name}
                                onChange={(e) =>
                                    setData('company_name', e.target.value)
                                }
                                placeholder="Spotless HR"
                            />
                            <InputError message={errors.company_name} />
                        </div>

                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="business_hours_start">
                                    Horário de Abertura *
                                </Label>
                                <Input
                                    id="business_hours_start"
                                    type="time"
                                    value={data.business_hours_start}
                                    onChange={(e) =>
                                        setData(
                                            'business_hours_start',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError
                                    message={errors.business_hours_start}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="business_hours_end">
                                    Horário de Fecho *
                                </Label>
                                <Input
                                    id="business_hours_end"
                                    type="time"
                                    value={data.business_hours_end}
                                    onChange={(e) =>
                                        setData(
                                            'business_hours_end',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError
                                    message={errors.business_hours_end}
                                />
                            </div>
                        </div>

                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="timezone">Fuso Horário *</Label>
                                <Select
                                    value={data.timezone}
                                    onValueChange={(value) =>
                                        setData('timezone', value)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {timezones.map((tz) => (
                                            <SelectItem
                                                key={tz.value}
                                                value={tz.value}
                                            >
                                                {tz.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.timezone} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="currency">Moeda *</Label>
                                <Select
                                    value={data.currency}
                                    onValueChange={(value) =>
                                        setData('currency', value)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {currencies.map((curr) => (
                                            <SelectItem
                                                key={curr.value}
                                                value={curr.value}
                                            >
                                                {curr.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.currency} />
                            </div>
                        </div>

                        <div className="flex justify-end">
                            <Button type="submit" disabled={processing}>
                                <Save className="mr-2 size-4" />
                                Guardar Configurações
                            </Button>
                        </div>
                    </form>
                </Card>

                <Card className="p-6">
                    <h2 className="mb-4 text-lg font-semibold">
                        Informações do Sistema
                    </h2>
                    <div className="space-y-2 text-sm">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Última atualização:
                            </span>
                            <span>
                                {new Date(settings.updated_at).toLocaleString(
                                    'pt-PT',
                                )}
                            </span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Data de criação:
                            </span>
                            <span>
                                {new Date(settings.created_at).toLocaleString(
                                    'pt-PT',
                                )}
                            </span>
                        </div>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
