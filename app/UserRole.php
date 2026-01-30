<?php

namespace App;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Manager => 'Gestor',
            self::Employee => 'Funcionário',
        };
    }

    public function canDelete(UserRole $target): bool
    {
        return match ($this) {
            self::Admin => true,
            self::Manager => $target !== self::Admin,
            self::Employee => false,
        };
    }

    public function canManageEmployees(): bool
    {
        return $this === self::Admin || $this === self::Manager;
    }

    public function canViewAllData(): bool
    {
        return $this === self::Admin || $this === self::Manager;
    }

    public function canMarkAttendance(): bool
    {
        return $this === self::Admin || $this === self::Manager;
    }
}
