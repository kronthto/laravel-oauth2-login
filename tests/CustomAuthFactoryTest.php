<?php

namespace Tests;

use Kronthto\LaravelOAuth2Login\OAuthProviderService;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class CustomAuthFactoryTest extends MiddlewareTest
{
    public function testUsesFactoryFromConfigToConstructUser()
    {
        config()->set('oauth2login.authWrapperFactory', DemoFactory::class);

        $token = new AccessToken([
            'access_token' => 'blabla',
            'expires' => time() + 100000,
        ]);

        /** @var OAuthProviderService|\PHPUnit_Framework_MockObject_MockObject $oauthProviderMock */
        $oauthProviderMock = $this->getMockBuilder(OAuthProviderService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userInfo = new GenericResourceOwner([
            'email' => 'foo2@bar.de',
        ], 'email');
        $oauthProviderMock->method('getTokenUser')->with($token)->willReturn($userInfo);
        $this->app->instance(OAuthProviderService::class, $oauthProviderMock);

        // Call to init Guard
        $this->withSession([
            config('oauth2login.session_key') => $token,
        ])->get('web/email');

        $authGuard = \Auth::guard('oauth2guard');
        $this->assertInstanceOf(DemoUserClass::class, $authGuard->user());
        $this->assertEquals('foo2@bar.de', $authGuard->user()->demokey);
    }
}

class DemoUserClass
{
    public $demokey;
}

class DemoFactory
{
    public function __invoke(ResourceOwnerInterface $resourceOwner)
    {
        $inst = new DemoUserClass();
        $inst->demokey = $resourceOwner->toArray()['email'];

        return $inst;
    }
}
