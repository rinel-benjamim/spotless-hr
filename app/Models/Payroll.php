<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reference_month',
        'base_salary',
        'total_days_worked',
        'absences_count',
        'late_count',
        'total_deductions',
        'total_bonus',
        'net_salary',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reference_month' => 'date',
            'paid_at' => 'datetime',
            'base_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_bonus' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }
}
