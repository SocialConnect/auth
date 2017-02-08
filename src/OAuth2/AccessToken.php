<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;

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
     * @throws InvalidAccessToken
     */
    public function __construct(array $token)
    {
        if (!isset($token['access_token'])) {
            throw new InvalidAccessToken(
                'API returned data without access_token field'
            );
        }

        $this->token = $token['access_token'];

        if (isset($token['expires'])) {
            $this->expires = $token['expires'];
        }

        if (isset($token['user_id'])) {
            $this->uid = $token['user_id'];
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

    /**
     * @return int|null
     */
    public function getExpires()
    {
        return $this->expires;
    }
}
