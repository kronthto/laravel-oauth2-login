<?php

namespace Tests;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Kronthto\LaravelOAuth2Login\OAuthProvider;
use Kronthto\LaravelOAuth2Login\OAuthProviderService;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;

class CheckOnlyMiddlewareTest extends MiddlewareTest
{
    protected function addWebRoutes(Router $router)
    {
        $router->group(['middleware' => \Kronthto\LaravelOAuth2Login\OnlyCheckOAuth::class], function (Router $router) {
            $router->get('web/ping', [
                'as' => 'web.ping',
                'uses' => function () {
                    return 'pong';
                },
            ]);
            $router->get('web/email', [
                'as' => 'web.email',
                'uses' => function (Request $request) {
                    $oauthData = $request->attributes->get(config('oauth2login.resource_owner_attribute'));

                    return $oauthData ? $oauthData->toArray()['email'] : 'not logged';
                },
            ]);
        });
    }

    public function testUnauthGetsRedirected()
    {
        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([])->get('web/email');
        $response->assertStatus(200);
        $response->assertSeeText('not logged');
        $response->assertSessionMissing(['url.intended', config('oauth2login.session_key_state')]);
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
            ->setMethods(cnull)
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
        ])->willThrowException(new IdentityProviderException(
            'Testing IdentityProviderExceptionHandling during refresh',
            401,
            'Refresh Token invalid or expired'
        ));
        $reflection_property->setValue($oauthProviderMock, $fakeProvider);

        $this->app->instance(OAuthProviderService::class, $oauthProviderMock);

        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([
            config('oauth2login.session_key') => $token,
        ])->get('web/ping');

        $response->assertStatus(200);
        $response->assertSeeText('pong');
        $response->assertSessionMissing(config('oauth2login.session_key'));
    }

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

        $oauthProviderMock->method('getTokenUser')->with($token)->willThrowException(new IdentityProviderException(
            'Testing IdentityProviderExceptionHandling',
            401,
            'Token revoked or soemthing'
        ));
        $this->app->instance(OAuthProviderService::class, $oauthProviderMock);

        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([
            config('oauth2login.session_key') => $token,
        ])->get('web/email');

        $response->assertSessionMissing(config('oauth2login.session_key'));
        $response->assertStatus(200);
        $response->assertSeeText('not logged');

        $authGuard = Auth::guard('oauth2guard');
        $this->assertFalse($authGuard->check());
    }
}
