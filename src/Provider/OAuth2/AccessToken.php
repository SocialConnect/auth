<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider\OAuth2;

class AccessToken
{
    /**
     * @var
     */
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }
}
