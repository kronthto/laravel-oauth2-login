<?php

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Routing\Router;
use Kronthto\LaravelOAuth2Login\CheckOAuth2;

class GlobalMiddlewareTest extends TestCase
{
    use InteractsWithSession;

    protected function getEnvironmentSetUp($app)
    {
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(\Illuminate\Session\Middleware\StartSession::class);
        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel->appendMiddlewareToGroup('web', CheckOAuth2::class);

        parent::getEnvironmentSetUp($app);
    }

    protected function addWebRoutes(Router $router)
    {
        $router->group(['middleware' => 'web'], function (Router $router) {
            parent::addWebRoutes($router);
        });
    }

    public function testMiddlewareDoesNotApplyonCallbackRoute()
    {
        $response = $this->get(config('oauth2login.oauth_redirect_path'));

        $response->assertStatus(400); // Rather than 302 to the authserver
    }

    public function testUnauthGetsRedirected()
    {
        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([])->get('web/ping');
        $response->assertStatus(302);
    }
}
