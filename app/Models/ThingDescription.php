<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель описаний вещей
 * Хранит историю всех описаний вещи с возможностью выбора актуального
 */
class ThingDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'thing_id',
        'description',
        'is_current',
        'created_by',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    /**
     * Получить вещь
     */
    public function thing(): BelongsTo
    {
        return $this->belongsTo(Thing::class);
    }

    /**
     * Получить пользователя, создавшего описание
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
