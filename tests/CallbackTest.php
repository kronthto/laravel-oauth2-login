<?php

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Kronthto\LaravelOAuth2Login\OAuthProvider;
use Kronthto\LaravelOAuth2Login\OAuthProviderService;
use League\OAuth2\Client\Token\AccessToken;

class CallbackTest extends TestCase
{
    use InteractsWithSession;

    /**
     * Requests with no / invalid state should be rejected.
     */
    public function testValidatesState()
    {
        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->get(config('oauth2login.oauth_redirect_path'));

        $response->assertStatus(400);
        $response->assertSeeText('Invalid state');

        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([config('oauth2login.session_key_state') => 'rndstate'])->get(config('oauth2login.oauth_redirect_path').'?state=wrongstate');

        $response->assertStatus(400);
        $response->assertSeeText('Invalid state');
    }

    /**
     * Test the authcallback.
     *
     * It should:
     * - Request a token
     * - Save it to session
     * - Redirect
     */
    public function testHappyPath()
    {
        $token = new AccessToken([
            'access_token' => 'blabla',
            'refresh_token' => 'beefbeef',
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
        $fakeProvider->expects($this->once())->method('getAccessToken')->with('authorization_code', [
            'code' => 'myauthcode',
        ])->willReturn($token);
        $reflection_property->setValue($oauthProviderMock, $fakeProvider);

        $this->app->instance(OAuthProviderService::class, $oauthProviderMock);

        /** @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->withSession([
            config('oauth2login.session_key_state') => 'correctstate',
            'url.intended' => 'http://example.com',
        ])->get(config('oauth2login.oauth_redirect_path').'?state=correctstate&code=myauthcode');

        $response->assertSessionHas(config('oauth2login.session_key'), $token);
        $response->assertRedirect('http://example.com');
    }
}
