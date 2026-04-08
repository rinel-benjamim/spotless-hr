import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren } from 'react';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
    pendingJustificationsCount = 0,
}: PropsWithChildren<{
    breadcrumbs?: BreadcrumbItem[];
    pendingJustificationsCount?: number;
}>) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar
                pendingJustificationsCount={pendingJustificationsCount}
            />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
