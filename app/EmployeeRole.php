<?php

namespace App;

enum EmployeeRole: string
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
}
