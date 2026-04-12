<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Justification extends Model
{
    use HasFactory;

    // Campos permitidos
    protected $fillable = [
        'attendance_id',
        'employee_id',
        'absence_date',
        'reason',
        'status',
        'justified_by',
    ];

    // Conversões de tipo
    protected function casts(): array
    {
        return [
            'absence_date' => 'date',
        ];
    }

    // Relation: attendance associada
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    // Relation: funcionário
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Relation: quem justificou
    public function justifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'justified_by');
    }
}
