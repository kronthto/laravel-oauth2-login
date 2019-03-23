<?php

namespace Kronthto\LaravelOAuth2Login;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use League\OAuth2\Client\Token\AccessToken;

class OAuthProviderService
{
    /** @var \League\OAuth2\Client\Provider\AbstractProvider */
    protected $provider;

    /**
     * CheckOAuth2 constructor.
     */
    public function __construct()
    {
        $providerClass = config('oauth2login.provider');

        $this->provider = new $providerClass(config('oauth2login.oauthconf'));
    }

    /**
     * @return \League\OAuth2\Client\Provider\AbstractProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Associates the token with the user.
     *
     * @param AccessToken $token
     */
    public function persistAccessToken(AccessToken $token)
    {
        session()->put(config('oauth2login.session_key'), $token);
    }

    /**
     * Builds a cache key for storing the resource owner details.
     *
     * @param AccessToken $token
     *
     * @return string
     */
    protected function getTokenUserCacheKey(AccessToken $token)
    {
        return config('oauth2login.cacheUserPrefix').sha1($token->getToken());
    }

    /**
     * Gets and caches the user details associated with a given token.
     *
     * @param AccessToken $token
     *
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function getTokenUser(AccessToken $token)
    {
        return Cache::remember(
            $this->getTokenUserCacheKey($token),
            Carbon::now()->addMinutes(config('oauth2login.cacheUserDetailsFor')),
            function () use ($token) {
                return $this->getProvider()->getResourceOwner($token);
            }
        );
    }
}
