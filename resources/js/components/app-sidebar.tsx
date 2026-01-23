import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Calendar,
    CalendarDays,
    Clock,
    DollarSign,
    FileText,
    Folder,
    LayoutGrid,
    Users,
    UserX,
} from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Funcionários',
        href: '/employees',
        icon: Users,
        isAdminOnly: true,
    },
    {
        title: 'Turnos',
        href: '/shifts',
        icon: Clock,
        isAdminOnly: true,
    },
    {
        title: 'Escalas',
        href: '/schedules',
        icon: CalendarDays,
    },
    {
        title: 'Presenças',
        href: '/attendances',
        icon: Calendar,
    },
    {
        title: 'Faltas',
        href: '/absences',
        icon: UserX,
    },
    {
        title: 'Folhas de Pagamento',
        href: '/payrolls',
        icon: DollarSign,
    },
    {
        title: 'Relatórios',
        href: '/reports',
        icon: FileText,
    },
];

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const isPrivileged =
        auth.user.role === 'admin' || auth.user.employee?.role === 'manager';

    const filteredNavItems = mainNavItems.filter((item) => {
        if (item.isAdminOnly && !isPrivileged) {
            return false;
        }
        return true;
    });

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={filteredNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
