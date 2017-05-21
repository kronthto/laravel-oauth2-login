<?php

namespace Kronthto\LaravelOAuth2Login;

use League\OAuth2\Client\Provider\GenericProvider;

class OAuthProvider extends GenericProvider
{
    protected function getDefaultHeaders()
    {
        return array_merge(parent::getDefaultHeaders(), [
            'Accept' => 'application/json',
            'User-Agent' => config('app.name', 'kronthto/laravel-oauth2-login'),
        ]);
    }
}
