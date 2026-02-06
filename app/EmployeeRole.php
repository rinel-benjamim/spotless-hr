<?php

namespace App;

enum EmployeeRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Employee = 'employee';
    case Washer = 'washer';
    case Ironer = 'ironer';
    case Attendant = 'attendant';
    case Driver = 'driver';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Diretor Geral',
            self::Manager => 'Gerente',
            self::Employee => 'Funcionário',
            self::Washer => 'Lavador',
            self::Ironer => 'Passador',
            self::Attendant => 'Atendente',
            self::Driver => 'Motorista',
        };
    }

    public function getUserRole(): string
    {
        return match ($this) {
            self::Admin => 'admin',
            self::Manager => 'manager',
            self::Employee, self::Washer, self::Ironer, self::Attendant, self::Driver => 'employee',
        };
    }
}
