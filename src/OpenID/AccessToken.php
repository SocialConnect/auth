<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OpenID;

use SocialConnect\Provider\AccessTokenInterface;

class AccessToken implements AccessTokenInterface
{
    /**
     * OpenID does not have a $token, it response $identity, it's a link to user
     *
     * @var string
     */
    protected $identity;

    /**
     * @var string|null
     */
    protected $uid;

    /**
     * @param string $identity
     * @param string|null $uid
     */
    public function __construct($identity, string $uid = null)
    {
        $this->identity = $identity;
        $this->uid = $uid;
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
        return $this->uid;
    }

    /**
     * @return int|null
     */
    public function getExpires()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {
        return null;
    }
}
