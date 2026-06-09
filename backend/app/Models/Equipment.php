<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'serial_number',
        'category',
        'status',
        'assigned_to',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => EquipmentStatus::class,
            'due_date' => 'date',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
