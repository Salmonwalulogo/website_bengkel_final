<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrapFive();

        View::composer([
            'frontend.*',
            'backend.layouts.*',
        ], function ($view): void {
            $logoPath = DB::table('settings')->where('key', 'site_logo')->value('value');

            $view->with(
                'siteLogoUrl',
                $logoPath ? asset('storage/' . $logoPath) : asset('images/logo-rumah-daun.png')
            );
        });
    }
}
