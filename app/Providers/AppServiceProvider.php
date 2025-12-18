<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
public function boot()
{
    View::composer('*', function ($view) {
        if (auth()->check()) {
            $layout = auth()->user()->role == 4
                ? 'layouts.supervisorapp'
                : 'layouts.app';

            $view->with('layout', $layout);
        }
    });
}
}
