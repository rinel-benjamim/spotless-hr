import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { Button } from '@/components/ui/button';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage<SharedData>().props;

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <Head title="Bem-vindo ao Spotless HR" />
            
            <header className="flex w-full items-center justify-between p-6 lg:px-12">
                <div className="flex items-center gap-2">
                    <AppLogo />
                </div>
                <nav className="flex items-center gap-4">
                    {auth.user ? (
                        <Link href={dashboard()}>
                            <Button variant="outline">Ir para o Dashboard</Button>
                        </Link>
                    ) : (
                        <>
                            <Link href={login()}>
                                <Button variant="ghost">Entrar</Button>
                            </Link>
                            {canRegister && (
                                <Link href={register()}>
                                    <Button>Registar</Button>
                                </Link>
                            )}
                        </>
                    )}
                </nav>
            </header>

            <main className="flex flex-1 flex-col items-center justify-center p-6 text-center lg:p-12">
                <div className="max-w-3xl space-y-8">
                    <h1 className="text-4xl font-extrabold tracking-tight lg:text-6xl">
                        Gestão de RH <span className="text-primary">Simplificada</span> e <span className="text-secondary">Profissional</span>
                    </h1>
                    <p className="text-xl text-muted-foreground">
                        A plataforma completa para gerir a sua equipa, escalas, presenças e processamento salarial com total eficiência.
                    </p>
                    <div className="flex flex-wrap justify-center gap-4 pt-4">
                        {auth.user ? (
                            <Link href={dashboard()}>
                                <Button size="lg" className="h-12 px-8 text-lg">
                                    Começar Agora
                                </Button>
                            </Link>
                        ) : (
                            <Link href={register()}>
                                <Button size="lg" className="h-12 px-8 text-lg">
                                    Criar Conta Grátis
                                </Button>
                            </Link>
                        )}
                        <Button size="lg" variant="outline" className="h-12 px-8 text-lg">
                            Saber Mais
                        </Button>
                    </div>
                </div>

                <div className="mt-20 grid w-full max-w-5xl gap-8 md:grid-cols-3">
                    <div className="rounded-2xl border border-primary/10 bg-card p-8 shadow-sm transition-all hover:shadow-md">
                        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary font-bold text-xl">
                            1
                        </div>
                        <h3 className="mb-2 text-xl font-bold text-primary">Controlo de Assiduidade</h3>
                        <p className="text-muted-foreground text-sm">
                            Registo de entradas e saídas em tempo real com tolerâncias personalizáveis por turno.
                        </p>
                    </div>
                    <div className="rounded-2xl border border-primary/10 bg-card p-8 shadow-sm transition-all hover:shadow-md">
                        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-secondary/20 text-secondary-foreground font-bold text-xl">
                            2
                        </div>
                        <h3 className="mb-2 text-xl font-bold text-primary">Escalas Dinâmicas</h3>
                        <p className="text-muted-foreground text-sm">
                            Criação e gestão de escalas semanais ou mensais para toda a sua equipa com facilidade.
                        </p>
                    </div>
                    <div className="rounded-2xl border border-primary/10 bg-card p-8 shadow-sm transition-all hover:shadow-md">
                        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-accent/30 text-accent-foreground font-bold text-xl">
                            3
                        </div>
                        <h3 className="mb-2 text-xl font-bold text-primary">Folha de Pagamento</h3>
                        <p className="text-muted-foreground text-sm">
                            Processamento automático de salários com descontos por faltas integrados diretamente.
                        </p>
                    </div>
                </div>
            </main>

            <footer className="w-full border-t border-primary/10 p-6 text-center text-sm text-muted-foreground">
                <p>&copy; {new Date().getFullYear()} Spotless HR. Todos os direitos reservados.</p>
            </footer>
        </div>
    );
}
