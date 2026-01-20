<?php

namespace App\Providers;

use App\Models\Place;
use App\Models\Thing;
use App\Policies\PlacePolicy;
use App\Policies\ThingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Place::class => PlacePolicy::class,
        Thing::class => ThingPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate для просмотра всех вещей (только админ)
        Gate::define('view-all-things', function ($user) {
            return $user->isAdmin();
        });

        // Gate для изменения вещи (хозяин или назначенный пользователь)
        Gate::define('update-thing', function ($user, $thing) {
            if ($user->isAdmin()) {
                return true;
            }
            
            // Хозяин может изменять
            if ($thing->master === $user->id) {
                return true;
            }
            
            // Назначенный пользователь может изменять
            return $thing->usages()
                ->where('user_id', $user->id)
                ->exists();
        });
    }
}
