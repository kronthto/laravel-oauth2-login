<?php

namespace Kronthto\LaravelOAuth2Login;

use Illuminate\Http\Request;

class AuthFromRequest
{
    /**
     * Extract the Auth from the Requests attributes and build an Authenticatable wrapper.
     *
     * @param Request $request
     *
     * @return null|\Illuminate\Contracts\Auth\Authenticatable
     */
    public function __invoke(Request $request)
    {
        $resourceOwner = $request->attributes->get(config('oauth2login.resource_owner_attribute'));

        if (!$resourceOwner) {
            return null;
        }

        $wrapperClass = config('oauth2login.authWrapper');

        return new $wrapperClass($resourceOwner);
    }
}
