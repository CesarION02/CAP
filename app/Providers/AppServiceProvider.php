<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\admin\menu;

class AppServiceProvider extends ServiceProvider
{   
    public function boot()
    {
        View::composer("theme.lte.aside", function ($view) {
            $menus = Menu::getMenu(true);
            $view->with('menusComposer', $menus);
        });
        View::share('theme', 'lte');
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
