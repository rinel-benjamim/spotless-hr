<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Classe responsável por Schedule.
 */
class Schedule extends Model
{
    use HasFactory;

    // Campos permitidos
    protected $fillable = [
        'employee_id',
        'date',
        'shift_id',
        'is_working_day',
        'notes',
    ];

    /**
     * Conversões de tipo.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'is_working_day' => 'boolean',
        ];
    }

    /**
     * Relation: funcionário.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relation: turno.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
