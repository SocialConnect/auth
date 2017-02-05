<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OpenID;

use SocialConnect\Auth\AccessTokenInterface;

class AccessToken implements AccessTokenInterface
{
    /**
     * OpenID doesnot have a $token, it response $identity, it's a link to user
     *
     * @var string
     */
    protected $identity;

    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->identity;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        // not supported for OpenId
        return null;
    }
}
