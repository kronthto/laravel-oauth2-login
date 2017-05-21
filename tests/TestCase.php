<?php

namespace Tests;

use Illuminate\Routing\Router;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Kronthto\LaravelOAuth2Login\ServiceProvider::class];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $app['config'];

        $config->set('app.url', 'http://app.testing');
    }

    protected function getEnvironmentSetUp($app)
    {
        $router = $app['router'];
        $this->addWebRoutes($router);
    }

    /**
     * Register the routes.
     *
     * @param Router $router
     */
    protected function addWebRoutes(Router $router)
    {
        $router->get('web/ping', [
            'as' => 'web.ping',
            'uses' => function () {
                return 'pong';
            },
        ]);
    }
}
