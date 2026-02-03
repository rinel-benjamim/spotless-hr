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
            self::Admin => 'Diretor Geral',
            self::Manager => 'Gerente',
            self::Employee => 'Funcionário',
        };
    }
}
