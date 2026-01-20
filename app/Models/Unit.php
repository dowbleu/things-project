<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель размерности количества вещей
 * Например: штуки (шт), килограммы (кг), литры (л)
 */
class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
    ];

    /**
     * Получить все использования с этой размерностью
     */
    public function usages(): HasMany
    {
        return $this->hasMany(Usage::class);
    }

    /**
     * Получить полное название с сокращением
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->abbreviation})";
    }
}
