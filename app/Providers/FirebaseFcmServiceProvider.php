<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FirebaseFcmServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path() . '/Helpers/FirebaseFcm.php';
    }

    public function boot()
    {
        //
    }
}
