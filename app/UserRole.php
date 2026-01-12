<?php

namespace App;

enum UserRole: string
{
    case Admin = 'admin';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Employee => 'Funcionário',
        };
    }
}
