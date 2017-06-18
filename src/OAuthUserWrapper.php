<?php

namespace Kronthto\LaravelOAuth2Login;

use Illuminate\Contracts\Auth\Authenticatable;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class OAuthUserWrapper implements Authenticatable
{
    /** @var ResourceOwnerInterface */
    protected $user;

    public function __construct(ResourceOwnerInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Proxy to the actual user properties.
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->user->toArray()[$name];
    }

    /**
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner()
    {
        return $this->user;
    }

    public function getAuthIdentifierName()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getAuthIdentifier()
    {
        return $this->user->getId();
    }

    public function getAuthPassword()
    {
        throw new \BadMethodCallException('Not available for OAuth users');
    }

    public function getRememberToken()
    {
        throw new \BadMethodCallException('Not available for OAuth users');
    }

    public function setRememberToken($value)
    {
        throw new \BadMethodCallException('Not available for OAuth users');
    }

    public function getRememberTokenName()
    {
        throw new \BadMethodCallException('Not available for OAuth users');
    }
}
