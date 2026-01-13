<?php

namespace App;

enum AbsenceType: string
{
    case Absence = 'absence';
    case Late = 'late';
    case Justified = 'justified';

    public function label(): string
    {
        return match ($this) {
            self::Absence => 'Falta',
            self::Late => 'Atraso',
            self::Justified => 'Justificado',
        };
    }
}
