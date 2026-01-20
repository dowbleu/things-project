<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thing extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'wrnt',
        'master',
    ];

    protected $casts = [
        'wrnt' => 'date',
    ];

    /**
     * Получить владельца вещи
     */
    public function masterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master');
    }

    /**
     * Получить все использования вещи
     */
    public function usages(): HasMany
    {
        return $this->hasMany(Usage::class);
    }

    /**
     * Получить все описания вещи
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(ThingDescription::class);
    }

    /**
     * Получить актуальное описание вещи
     */
    public function currentDescription(): BelongsTo
    {
        return $this->belongsTo(ThingDescription::class, 'id', 'thing_id')
            ->where('is_current', true);
    }

    /**
     * Получить актуальное описание или первое доступное
     */
    public function getCurrentDescriptionAttribute(): ?string
    {
        $current = $this->descriptions()->where('is_current', true)->first();
        if ($current) {
            return $current->description;
        }
        return $this->description;
    }
}
