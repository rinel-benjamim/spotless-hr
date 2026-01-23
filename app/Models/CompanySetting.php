<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
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

    public static function current(): ?self
    {
        return self::first();
    }
}
