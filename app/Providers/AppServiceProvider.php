<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Menu;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Bagikan pengaturan situs (nama, logo, sosial, dsb.) ke semua tampilan.
        View::composer('*', function ($view) {
            if (! isset($view->getData()['site'])) {
                $view->with('site', SiteSettings::values());
            }
        });

        // Bagikan daftar kategori ke seluruh tampilan publik (menu navigasi).
        View::composer('public.*', function ($view) {
            if (! isset($view->getData()['navCategories'])) {
                $view->with('navCategories', Category::ordered()->take(8)->get());
            }

            if (! isset($view->getData()['navMenu'])) {
                $view->with('navMenu', Menu::active()->roots()->ordered()
                    ->with(['children' => fn ($query) => $query->where('is_active', true)])
                    ->get());
            }
        });
    }
}
