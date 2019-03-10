<?php

namespace Tests;

use Kronthto\LaravelOAuth2Login\OAuthProvider;
use Kronthto\LaravelOAuth2Login\OAuthProviderService;
use League\OAuth2\Client\Token\AccessToken;

class OAuthProviderServiceTest extends TestCase
{
    /** @var AccessToken */
    protected $tokenA;
    /** @var AccessToken */
    protected $tokenB;
    /** @var OAuthProviderService|\PHPUnit_Framework_MockObject_MockObject */
    protected $oauthProviderMock;

    /**
     * Assert the data returned is corrent even after being cached.
     * This means it covers the cache key generation works as intended.
     *
     * That the caching itself works is covered by the exactly(twice) expectation set up for getResourceOwner.
     */
    public function testCacheReturnsCorrectData()
    {
        $this->tokenA = new AccessToken([
            'access_token' => 'a',
            'expires' => time() + 100000,
        ]);
        $this->tokenB = new AccessToken([
            'access_token' => 'b',
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
        $fakeProvider->expects($this->exactly(2))->method('getResourceOwner')->willReturnCallback(function (
            AccessToken $token
        ) {
            return $token->getToken().'data';
        });
        $reflection_property->setValue($oauthProviderMock, $fakeProvider);

        $this->oauthProviderMock = $oauthProviderMock;
        $this->oauthProviderMock->getTokenUser($this->tokenA);
        $this->oauthProviderMock->getTokenUser($this->tokenB);
        // setUp done

        $this->assertSame('adata', $this->oauthProviderMock->getTokenUser($this->tokenA));
        $this->assertSame('bdata', $this->oauthProviderMock->getTokenUser($this->tokenB));
    }
}
