<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    // Campos permitidos
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'tolerance_minutes',
        'description',
    ];

    // Conversões de tipo
    protected function casts(): array
    {
        return [
            'tolerance_minutes' => 'integer',
        ];
    }

    // Relation: funcionários com este turno
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
