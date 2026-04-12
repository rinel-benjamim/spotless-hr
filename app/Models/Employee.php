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

    // Campos temporários para criação
    public ?string $temp_password = null;

    public ?string $email = null;

    // Campos permitidos em mass assignment
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

    // Conversões de tipo automático
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

    // Converte roles antigos para novos
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($employee) {
            if (is_string($employee->role)) {
                $roleMapping = [
                    'operator' => EmployeeRole::Employee,
                    'washer' => EmployeeRole::Employee,
                    'ironer' => EmployeeRole::Employee,
                    'supervisor' => EmployeeRole::Manager,
                    'delivery_driver' => EmployeeRole::Employee,
                    'customer_service' => EmployeeRole::Employee,
                ];

                if (isset($roleMapping[$employee->role])) {
                    $employee->role = $roleMapping[$employee->role];
                }
            }
        });
    }

    // Relation: usuário associado
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relation: turno padrão
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    // Relation: registros de ponto
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // Relation: justificativas
    public function justifications(): HasMany
    {
        return $this->hasMany(Justification::class);
    }

    // Relation: folhas de pagamento
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    // Relation: escalas
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    // Verifica se funcionário está ativo
    public function isActive(): bool
    {
        return $this->status === EmployeeStatus::Active;
    }

    // Verifica se é gerente
    public function isManager(): bool
    {
        return $this->role === EmployeeRole::Manager;
    }
}
