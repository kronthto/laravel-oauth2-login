<?php

namespace Tests;

use Auth;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Kronthto\LaravelOAuth2Login\OAuthProvider;
use Kronthto\LaravelOAuth2Login\OAuthProviderService;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use function GuzzleHttp\Psr7\parse_query;

class MiddlewareTest extends TestCase
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
                    return $request->attributes->get(config('oauth2login.resource_owner_attribute'))->toArray()['email'];
                },
            ]);
        });
    }

    /**
     * Unauthenticated should be redirected to the authorize endpoint.
     * It should remember the intended URL and save the state to session.
     */
    public function testUnauthGetsRedirected()
    {
        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([])->get('web/ping');
        $response->assertStatus(302);

        $redirect = $response->headers->get('Location');

        $this->assertStringStartsWith('http://brentertainment.com/oauth2/lockdin/authorize', $redirect);
        $response->assertSessionHas('url.intended', 'http://app.testing/web/ping');

        $params = parse_query(parse_url($redirect, PHP_URL_QUERY));
        $response->assertSessionHas(config('oauth2login.session_key_state'), $params['state']);
    }

    /**
     * If the token is correct we expect it to be added as an request attribute.
     * Also, the Auth guard should know about the successful auth.
     */
    public function testAddsUserinfoToRequest()
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

        $response->assertSeeText('foo@bar.de');

        $authGuard = Auth::guard('oauth2guard');
        $this->assertTrue($authGuard->check());
        $this->assertSame($userInfo, $authGuard->user()->getResourceOwner());
        $this->assertSame('foo@bar.de', $authGuard->user()->email);
        $this->assertSame('foo@bar.de', $authGuard->id());
    }

    /**
     * A token that is expired should be refreshed and persisted.
     * The request should not fail.
     */
    public function testRefreshesExpiredToken()
    {
        $token = new AccessToken([
            'access_token' => 'blabla',
            'refresh_token' => 'beefbeef',
            'expires' => time() - 100000,
        ]);
        $refreshedToken = new AccessToken([
            'access_token' => 'refreshed',
            'refresh_token' => 'beefier',
            'expires' => time() + 100000,
        ]);

        /** @var OAuthProviderService|\PHPUnit_Framework_MockObject_MockObject $oauthProviderMock */
        $oauthProviderMock = $this->getMockBuilder(OAuthProviderService::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $reflection = new \ReflectionClass($oauthProviderMock);
        $reflection_property = $reflection->getProperty('provider');
        $reflection_property->setAccessible(true);

        /** @var OAuthProvider|\PHPUnit_Framework_MockObject_MockObject $fakeProvider */
        $fakeProvider = $this->getMockBuilder(OAuthProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fakeProvider->expects($this->once())->method('getAccessToken')->with('refresh_token', [
            'refresh_token' => $token->getRefreshToken(),
        ])->willReturn($refreshedToken);
        $reflection_property->setValue($oauthProviderMock, $fakeProvider);

        $this->app->instance(OAuthProviderService::class, $oauthProviderMock);

        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([
            config('oauth2login.session_key') => $token,
        ])->get('web/ping');

        $response->assertSeeText('pong');
        $response->assertSessionHas(config('oauth2login.session_key'), $refreshedToken);
    }

    /**
     * Errors during refreshing (expired token) should not crash the whole request.
     */
    public function testRefreshTokenErrorHandling()
    {
        $token = new AccessToken([
            'access_token' => 'blabla',
            'refresh_token' => 'invalidorexpired',
            'expires' => time() - 100000,
        ]);

        /** @var OAuthProviderService|\PHPUnit_Framework_MockObject_MockObject $oauthProviderMock */
        $oauthProviderMock = $this->getMockBuilder(OAuthProviderService::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $reflection = new \ReflectionClass($oauthProviderMock);
        $reflection_property = $reflection->getProperty('provider');
        $reflection_property->setAccessible(true);

        /** @var OAuthProvider|\PHPUnit_Framework_MockObject_MockObject $fakeProvider */
        $fakeProvider = $this->getMockBuilder(OAuthProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fakeProvider->expects($this->once())->method('getAccessToken')->with('refresh_token', [
            'refresh_token' => $token->getRefreshToken(),
        ])->willThrowException(new IdentityProviderException('Testing IdentityProviderExceptionHandling during refresh', 401, 'Refresh Token invalid or expired'));
        $reflection_property->setValue($oauthProviderMock, $fakeProvider);

        $this->app->instance(OAuthProviderService::class, $oauthProviderMock);

        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([
            config('oauth2login.session_key') => $token,
        ])->get('web/ping');

        $response->assertStatus(302);
        $response->assertSessionMissing(config('oauth2login.session_key'));
    }

    /**
     * Unauthenticated should be redirected to the authorize endpoint.
     * The invalid token should be removed from session.
     */
    public function testRejectsInvalidAuthAndRedirects()
    {
        $token = new AccessToken([
            'access_token' => 'blabla',
            'expires' => time() + 100000,
        ]);

        /** @var OAuthProviderService|\PHPUnit_Framework_MockObject_MockObject $oauthProviderMock */
        $oauthProviderMock = $this->getMockBuilder(OAuthProviderService::class)
            ->setMethods(['getTokenUser'])
            ->getMock();

        $oauthProviderMock->method('getTokenUser')->with($token)->willThrowException(new IdentityProviderException('Testing IdentityProviderExceptionHandling',
            401, 'Token revoked or soemthing'));
        $this->app->instance(OAuthProviderService::class, $oauthProviderMock);

        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([
            config('oauth2login.session_key') => $token,
        ])->get('web/ping');

        $response->assertSessionMissing(config('oauth2login.session_key'));
        $response->assertStatus(302);

        $authGuard = Auth::guard('oauth2guard');
        $this->assertFalse($authGuard->check());
    }
}
