import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    role: 'admin' | 'employee';
    employee?: Employee;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface Employee {
    id: number;
    user_id: number | null;
    employee_code: string;
    full_name: string;
    role: EmployeeRole;
    contract_type: ContractType;
    shift_id: number | null;
    status: EmployeeStatus;
    user?: User;
    shift?: Shift;
    attendances?: Attendance[];
    created_at: string;
    updated_at: string;
}

export interface Shift {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
    description: string | null;
    employees_count?: number;
    created_at: string;
    updated_at: string;
}

export interface Attendance {
    id: number;
    employee_id: number;
    type: AttendanceType;
    recorded_at: string;
    notes: string | null;
    employee?: Employee;
    created_at: string;
    updated_at: string;
}

export type EmployeeRole =
    | 'manager'
    | 'supervisor'
    | 'operator'
    | 'washer'
    | 'ironer'
    | 'delivery_driver'
    | 'customer_service';

export type ContractType =
    | 'full_time'
    | 'part_time'
    | 'temporary'
    | 'internship';

export type EmployeeStatus = 'active' | 'inactive';

export type AttendanceType = 'check_in' | 'check_out';

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}
