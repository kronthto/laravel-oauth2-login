<?php

Route::get(config('oauth2login.oauth_redirect_path'),
    'Kronthto\LaravelOAuth2Login\OAuthLoginController@authorizeCallback')->middleware('web');
