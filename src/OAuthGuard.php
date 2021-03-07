<?php

namespace Kronthto\LaravelOAuth2Login;

class OAuthGuard extends \Illuminate\Auth\RequestGuard
{
    public function logout()
    {
        session()->remove(config('oauth2login.session_key'));
        $this->request->attributes->remove(config('oauth2login.resource_owner_attribute'));
        // dispatch Logout event?
        $this->user = null;
    }
}
