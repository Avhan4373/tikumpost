<?php

namespace App\Providers;

use App\Models\Category;
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
        // Bagikan daftar kategori ke seluruh tampilan publik (menu navigasi).
        View::composer('public.*', function ($view) {
            if (! isset($view->getData()['navCategories'])) {
                $view->with('navCategories', Category::ordered()->take(8)->get());
            }
        });
    }
}
