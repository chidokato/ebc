<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\View::composer('home', function ($view) {
            $view->with('popupSettings', \App\Models\PopupSetting::current());
        });
        \Illuminate\Support\Facades\View::composer(['home', 'news.layout'], function ($view) {
            $view->with('websiteSettings', \App\Models\WebsiteSetting::current());
        });
    }
}
