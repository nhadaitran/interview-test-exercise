<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'equipment_id',
        'user_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
