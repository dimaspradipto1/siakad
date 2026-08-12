<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\ProfilSekolah;

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
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('profil_sekolahs')) {
                    $schoolProfile = ProfilSekolah::query()->first();
                    $view->with('schoolProfile', $schoolProfile);
                }
            } catch (\Throwable $e) {
                // Ignore database errors during migrations or console commands
            }
        });
    }
}

