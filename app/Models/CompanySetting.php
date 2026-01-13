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
    ];

    public static function current(): ?self
    {
        return self::first();
    }
}
