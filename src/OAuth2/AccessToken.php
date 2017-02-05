<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2;

use InvalidArgumentException;
use SocialConnect\Auth\AccessTokenInterface;

class AccessToken implements AccessTokenInterface
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var integer|null
     */
    protected $uid;

    /**
     * @param string $token
     * @throws \InvalidArgumentException
     */
    public function __construct($token)
    {
        if (!is_string($token)) {
            throw new InvalidArgumentException(
                '$token must be a string, passed: ' . gettype($token)
            );
        }

        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param int|null $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->uid;
    }
}
