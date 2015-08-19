<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider\OpenID;

use InvalidArgumentException;

class AccessToken
{
    /**
     * @var string
     */
    protected $token;

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
}
