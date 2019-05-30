<?php

namespace Kronthto\LaravelOAuth2Login;

use Closure;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class OnlyCheckOAuth extends CheckOAuth2
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \League\OAuth2\Client\Token\AccessToken $auth */
        $auth = $request->session()->get(config('oauth2login.session_key'));
        if (!$auth) {
            return $next($request);
        }

        try {
            $auth = $this->refreshTokenIfNecessary($auth);
            $resourceOwner = $this->oauthService->getTokenUser($auth);
        } catch (IdentityProviderException $e) {
            $request->session()->remove(config('oauth2login.session_key'));

            return $next($request);
        }

        $request->attributes->add([config('oauth2login.resource_owner_attribute') => $resourceOwner]);

        return $next($request);
    }

    protected function getAuthRedirect()
    {
        throw new \BadMethodCallException('Unexpected call / Redirect should not happen on OnlyCheck');
    }
}
