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
    isAdminOnly?: boolean;
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
    role: 'admin' | 'manager' | 'employee';
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
    hire_date: string | null;
    base_salary: number | null;
    deduction_per_absence: number | null;
    status: EmployeeStatus;
    user?: User;
    shift?: Shift;
    attendances?: Attendance[];
    justifications?: Justification[];
    payrolls?: Payroll[];
    schedules?: Schedule[];
    created_at: string;
    updated_at: string;
}

export interface Shift {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
    tolerance_minutes: number;
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

export type EmployeeRole = 'admin' | 'manager' | 'employee' | 'washer' | 'ironer' | 'attendant' | 'driver';

export type ContractType =
    | 'full_time'
    | 'part_time'
    | 'temporary'
    | 'internship';

export type EmployeeStatus = 'active' | 'inactive';

export type AttendanceType = 'check_in' | 'check_out';

export interface Justification {
    id: number;
    employee_id: number;
    attendance_id: number | null;
    absence_date: string | null;
    reason: string;
    justified_by: number;
    employee?: Employee;
    attendance?: Attendance;
    justifiedBy?: User;
    created_at: string;
    updated_at: string;
}

export interface ActivityLog {
    id: number;
    user_id: number | null;
    action: string;
    model_type: string | null;
    model_id: number | null;
    description: string | null;
    properties: Record<string, unknown> | null;
    ip_address: string | null;
    user?: User;
    created_at: string;
    updated_at: string;
}

export interface CompanySetting {
    id: number;
    company_name: string;
    business_hours_start: string;
    business_hours_end: string;
    timezone: string;
    currency: string;
    created_at: string;
    updated_at: string;
}

export interface MonthlySummary {
    days_worked: number;
    late_count: number;
    absence_count: number;
    justified_count: number;
    total_hours: number;
}

export interface Payroll {
    id: number;
    employee_id: number;
    reference_month: string;
    base_salary: number;
    total_days_worked: number;
    absences_count: number;
    late_count: number;
    total_deductions: number;
    total_bonus: number;
    net_salary: number;
    paid_at: string | null;
    notes: string | null;
    employee?: Employee;
    created_at: string;
    updated_at: string;
}

export interface Schedule {
    id: number;
    employee_id: number;
    date: string;
    shift_id: number | null;
    is_working_day: boolean;
    notes: string | null;
    employee?: Employee;
    shift?: Shift;
    created_at: string;
    updated_at: string;
}

export type AbsenceType = 'absence' | 'late' | 'justified';

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface Report {
    id: number;
    created_by: number;
    title: string;
    description: string | null;
    type: 'attendance' | 'payroll' | 'schedule' | 'general';
    data: Record<string, unknown> | null;
    file_path: string | null;
    status: 'pending' | 'completed' | 'failed';
    generated_at: string | null;
    creator?: User;
    created_at: string;
    updated_at: string;
}
