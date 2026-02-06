<?php

namespace App;

enum ContractType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Temporary = 'temporary';
    case Freelance = 'freelance';
    case Internship = 'internship';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Tempo Integral',
            self::PartTime => 'Meio Período',
            self::Temporary => 'Temporário',
            self::Freelance => 'Freelancer',
            self::Internship => 'Estágio',
        };
    }
}
