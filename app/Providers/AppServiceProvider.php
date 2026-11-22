<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enable strict checks for lazy-loaded relations outside production.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
