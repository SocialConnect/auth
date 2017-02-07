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
     * @var int|null
     */
    protected $expires;

    /**
     * @var integer|null
     */
    protected $uid;

    /**
     * @param array $token
     * @throws \InvalidArgumentException
     */
    public function __construct(array $token)
    {
        if (!isset($token['access_token'])) {
            throw new InvalidArgumentException(
                '$token must be a string, passed: ' . gettype($token)
            );
        }

        $this->token = $token['access_token'];

        if (isset($token['expires'])) {
            $this->expires = $token['expires'];
        }
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
