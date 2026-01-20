<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель архива удаленных вещей
 * Сохраняет информацию о вещи на момент удаления для возможности восстановления
 */
class ArchivedThing extends Model
{
    use HasFactory;

    protected $fillable = [
        'thing_name',
        'current_description',
        'master_name',
        'last_user_name',
        'place_name',
        'is_restored',
        'restored_by',
        'restored_at',
    ];

    protected $casts = [
        'is_restored' => 'boolean',
        'restored_at' => 'datetime',
    ];

    /**
     * Получить пользователя, восстановившего вещь
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}
