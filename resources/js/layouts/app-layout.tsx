import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    pendingJustificationsCount?: number;
}

export default ({
    children,
    breadcrumbs,
    pendingJustificationsCount = 0,
    ...props
}: AppLayoutProps) => (
    <AppLayoutTemplate
        breadcrumbs={breadcrumbs}
        pendingJustificationsCount={pendingJustificationsCount}
        {...props}
    >
        {children}
    </AppLayoutTemplate>
);
