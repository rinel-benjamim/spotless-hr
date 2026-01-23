<?php

namespace App\Models;

use App\ContractType;
use App\EmployeeRole;
use App\EmployeeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    public ?string $temp_password = null;

    public ?string $email = null;

    protected $fillable = [
        'user_id',
        'employee_code',
        'full_name',
        'role',
        'contract_type',
        'shift_id',
        'hire_date',
        'base_salary',
        'deduction_per_absence',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'role' => EmployeeRole::class,
            'contract_type' => ContractType::class,
            'status' => EmployeeStatus::class,
            'hire_date' => 'date',
            'base_salary' => 'decimal:2',
            'deduction_per_absence' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function justifications(): HasMany
    {
        return $this->hasMany(Justification::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function isActive(): bool
    {
        return $this->status === EmployeeStatus::Active;
    }

    public function isManager(): bool
    {
        return $this->role === EmployeeRole::Manager;
    }
}
