<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    // Campos permitidos
    protected $fillable = [
        'company_name',
        'logo_path',
        'business_hours_start',
        'business_hours_end',
        'timezone',
        'currency',
        'late_deduction_amount',
        'early_exit_deduction_amount',
    ];

    // Obtém as configurações atuais
    public static function current(): ?self
    {
        return self::first();
    }
}
