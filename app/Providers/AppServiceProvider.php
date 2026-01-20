<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Директива для выделения вещей аутентифицированного пользователя
        Blade::directive('highlightUserThing', function ($expression) {
            return "<?php 
                \$thing = {$expression};
                \$userId = auth()->id();
                if (\$thing->master === \$userId) {
                    echo 'class=\"table-warning\"';
                }
            ?>";
        });

        // Директива для выделения текущей вкладки
        Blade::directive('activeTab', function ($expression) {
            return "<?php 
                \$route = {$expression};
                if (request()->routeIs(\$route)) {
                    echo 'active';
                }
            ?>";
        });

        // Директива для выделения вещей в специальных местах
        Blade::directive('highlightPlaceThing', function ($expression) {
            return "<?php 
                \$thing = {$expression};
                if (!isset(\$thing->usages)) {
                    \$thing->load('usages.place');
                }
                \$hasRepair = \$thing->usages->contains(function(\$usage) {
                    return \$usage->place && \$usage->place->repair;
                });
                \$hasWork = \$thing->usages->contains(function(\$usage) {
                    return \$usage->place && \$usage->place->work;
                });
                if (\$hasRepair) {
                    echo 'class=\"table-danger\"';
                } elseif (\$hasWork) {
                    echo 'class=\"table-info\"';
                }
            ?>";
        });
    }
}
