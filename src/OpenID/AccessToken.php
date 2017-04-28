<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

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
     * @var integer|null
     */
    protected $uid;

    /**
     * @param string $identity
     * @param int|null $uid
     */
    public function __construct($identity, $uid = null)
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
}
