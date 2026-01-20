<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usage extends Model
{
    use HasFactory;

    protected $fillable = [
        'thing_id',
        'place_id',
        'user_id',
        'amount',
        'unit_id',
    ];

    /**
     * Получить вещь
     */
    public function thing(): BelongsTo
    {
        return $this->belongsTo(Thing::class);
    }

    /**
     * Получить место хранения
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Получить пользователя, использующего вещь
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить размерность количества
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Получить количество с размерностью
     */
    public function getAmountWithUnitAttribute(): string
    {
        if ($this->unit) {
            return "{$this->amount} {$this->unit->abbreviation}";
        }
        return (string) $this->amount;
    }
}
