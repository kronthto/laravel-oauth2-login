<?php

namespace Kronthto\LaravelOAuth2Login;

use Closure;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;

class CheckOAuth2
{
    /** @var OAuthProviderService */
    protected $oauthService;

    /**
     * CheckOAuth2 constructor.
     *
     * @param OAuthProviderService $oauthService
     */
    public function __construct(OAuthProviderService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function handle(Request $request, Closure $next)
    {
        /** @var AccessToken $auth */
        $auth = $request->session()->get(config('oauth2login.session_key'));

        if (!$auth) {
            return $this->getAuthRedirect();
        }

        $auth = $this->refreshTokenIfNecessary($auth);

        try {
            $resourceOwner = $this->oauthService->getTokenUser($auth);
        } catch (IdentityProviderException $e) {
            $request->session()->remove(config('oauth2login.session_key'));

            return $this->getAuthRedirect();
        }

        $request->attributes->add([config('oauth2login.resource_owner_attribute') => $resourceOwner]);

        return $next($request);
    }

    /**
     * @param AccessToken $token
     *
     * @return AccessToken
     */
    protected function refreshTokenIfNecessary(AccessToken $token)
    {
        if ($token->hasExpired()) {
            $token = $this->oauthService->getProvider()->getAccessToken('refresh_token', [
                'refresh_token' => $token->getRefreshToken(),
            ]);

            $this->oauthService->persistAccessToken($token);
        }

        return $token;
    }

    /**
     * Generates a state and return the redirect URL.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function getAuthRedirect()
    {
        $authorizationUrl = $this->oauthService->getProvider()->getAuthorizationUrl();

        session()->put(config('oauth2login.session_key_state'), $this->oauthService->getProvider()->getState());

        return redirect()->guest($authorizationUrl);
    }
}
