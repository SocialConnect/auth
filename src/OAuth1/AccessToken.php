<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1;

use SocialConnect\Provider\AccessTokenInterface;

/**
 * Class AccessToken
 */
class AccessToken extends \SocialConnect\OAuth1\Token implements AccessTokenInterface
{
    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $screenName;

    /**
     * @var int
     */
    protected $x_auth_expires = 0;

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getScreenName()
    {
        return $this->screenName;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param string $screenName
     */
    public function setScreenName(string $screenName)
    {
        $this->screenName = $screenName;
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        // It's a key, not a secret
        return $this->key;
    }

    /**
     * @return int|null
     */
    public function getExpires()
    {
        // @todo support
        return null;
    }
}
