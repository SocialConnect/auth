<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider\OAuth2;

use InvalidArgumentException;

class AccessToken
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
     * @return int|null
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param int|null $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }
}
