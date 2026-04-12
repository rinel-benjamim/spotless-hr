<?php

namespace App\Models;

use App\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    // Campos permitidos
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    // Campos ocultos (serialização)
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    // Conversões de tipo
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'role' => UserRole::class,
        ];
    }

    // Relation: funcionário associado
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    // Verifica se é admin
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    // Verifica se é gerente
    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    // Verifica se é funcionário
    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    // Verifica se pode excluir outro usuário
    public function canDelete(User $target): bool
    {
        return $this->role->canDelete($target->role);
    }

    // Verifica se pode gerenciar funcionários
    public function canManageEmployees(): bool
    {
        return $this->role->canManageEmployees();
    }

    // Verifica se pode ver todos os dados
    public function canViewAllData(): bool
    {
        return $this->role->canViewAllData();
    }

    // Verifica se pode marcar ponto
    public function canMarkAttendance(): bool
    {
        return $this->role->canMarkAttendance();
    }
}
