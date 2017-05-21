<?php

return [
    'oauthconf' => [ // See http://oauth2-client.thephpleague.com/usage/#authorization-code-flow
        'clientId' => 'demoapp', // The client ID assigned to you by the provider
        'clientSecret' => 'demopass', // The client password assigned to you by the provider
        'redirectUri' => 'http://example.com/your-redirect-url/',
        'urlAuthorize' => 'http://brentertainment.com/oauth2/lockdin/authorize',
        'urlAccessToken' => 'http://brentertainment.com/oauth2/lockdin/token',
        'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource',
    ],

    'oauth_redirect_path' => '/oauth2/callback',

    'session_key' => 'oauth2_session',
    'session_key_state' => 'oauth2_auth_state',

    'resource_owner_attribute' => 'oauth2_user',

    'cacheUserDetailsFor' => 30, // minutes
    'cacheUserPrefix' => 'oauth_user_',
];
