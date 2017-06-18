<?php

namespace Kronthto\LaravelOAuth2Login;

use Auth;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $defer = false;

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'oauth2login');
    }

    public function boot()
    {
        $this->publishes([$this->configPath() => config_path('oauth2login.php')]);
        $this->loadRoutesFrom($this->resourcePath().'routes.php');
        Auth::viaRequest(config('oauth2login.auth_driver_key'), app(AuthFromRequest::class));
    }

    /** @return string */
    protected function configPath()
    {
        return $this->resourcePath().'config.php';
    }

    /** @return string */
    protected function resourcePath()
    {
        return __DIR__.'/../resources/';
    }
}
