<?php

namespace App;

enum EmployeeRole: string
{
    case Manager = 'manager';
    case Supervisor = 'supervisor';
    case Operator = 'operator';
    case Washer = 'washer';
    case Ironer = 'ironer';
    case DeliveryDriver = 'delivery_driver';
    case CustomerService = 'customer_service';

    public function label(): string
    {
        return match ($this) {
            self::Manager => 'Gerente',
            self::Supervisor => 'Supervisor',
            self::Operator => 'Operador',
            self::Washer => 'Lavador',
            self::Ironer => 'Passador',
            self::DeliveryDriver => 'Motorista de Entrega',
            self::CustomerService => 'Atendimento ao Cliente',
        };
    }
}
