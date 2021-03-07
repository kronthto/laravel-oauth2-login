<?php

namespace Tests;

use Auth;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Kronthto\LaravelOAuth2Login\OAuthProviderService;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;

class LogoutTest extends TestCase
{
    use InteractsWithSession;

    protected function getEnvironmentSetUp($app)
    {
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(\Illuminate\Session\Middleware\StartSession::class);

        parent::getEnvironmentSetUp($app);
    }

    protected function addWebRoutes(Router $router)
    {
        $router->group(['middleware' => \Kronthto\LaravelOAuth2Login\CheckOAuth2::class], function (Router $router) {
            parent::addWebRoutes($router);
            $router->get('web/email', [
                'as' => 'web.email',
                'uses' => function (Request $request) {
                    \Auth::guard('oauth2guard')->logout();
                    return 'ok';
                },
            ]);
        });
    }

    public function testLogout()
    {
        $token = new AccessToken([
            'access_token' => 'blabla',
            'expires' => time() + 100000,
        ]);

        /** @var OAuthProviderService|\PHPUnit_Framework_MockObject_MockObject $oauthProviderMock */
        $oauthProviderMock = $this->getMockBuilder(OAuthProviderService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userInfo = new GenericResourceOwner([
            'email' => 'foo@bar.de',
        ], 'email');
        $oauthProviderMock->method('getTokenUser')->with($token)->willReturn($userInfo);
        $this->app->instance(OAuthProviderService::class, $oauthProviderMock);

        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([
            config('oauth2login.session_key') => $token,
        ])->get('web/email');

        $authGuard = Auth::guard('oauth2guard');

        $this->assertFalse($authGuard->check());
        $response->assertSessionMissing(config('oauth2login.session_key'));
    }
}
