<?php

namespace Kronthto\LaravelOAuth2Login;

use Illuminate\Http\Request;

class OAuthLoginController
{
    /** @var OAuthProviderService */
    protected $oauthService;

    /**
     * OAuthLoginController constructor.
     *
     * @param OAuthProviderService $oauthService
     */
    public function __construct(OAuthProviderService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function authorizeCallback(Request $request)
    {
        if (!$request->has('state') || $request->get('state') !== $request->session()->get(config('oauth2login.session_key_state'))) {
            return response('Invalid state', 400);
        }

        $accessToken = $this->oauthService->getProvider()->getAccessToken('authorization_code', [
            'code' => $request->get('code'),
        ]);

        $this->oauthService->persistAccessToken($accessToken);

        return redirect()->intended();
    }
}
