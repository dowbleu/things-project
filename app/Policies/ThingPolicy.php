<?php

namespace App\Policies;

use App\Models\Thing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ThingPolicy
{
    /**
     * Determine whether the user can view any models.
     * Только администратор может просматривать весь список вещей
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     * Только администратор может просматривать вещь
     */
    public function view(User $user, Thing $thing): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     * Все аутентифицированные пользователи могут создавать вещи
     */
    public function create(User $user): bool
    {
        return true; // Все аутентифицированные пользователи могут создавать вещи
    }

    /**
     * Determine whether the user can update the model.
     * Остальные методы недоступны (даже админу через Policy)
     */
    public function update(User $user, Thing $thing): bool
    {
        return false; // Недоступно через Policy, используется Gate
    }

    /**
     * Determine whether the user can delete the model.
     * Остальные методы недоступны (даже админу через Policy)
     */
    public function delete(User $user, Thing $thing): bool
    {
        return false; // Недоступно через Policy, используется Gate
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Thing $thing): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Thing $thing): bool
    {
        //
    }
}
