<?php

namespace App\Models;

use App\AttendanceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Classe responsável por Attendance.
 */
class Attendance extends Model
{
    use HasFactory;

    // Campos permitidos
    protected $fillable = [
        'employee_id',
        'type',
        'recorded_at',
        'notes',
    ];

    /**
     * Conversões de tipo.
     */
    protected function casts(): array
    {
        return [
            'type' => AttendanceType::class,
            'recorded_at' => 'datetime',
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
     * Relation: justificativa associada.
     */
    public function justification(): HasOne
    {
        return $this->hasOne(Justification::class);
    }
}
