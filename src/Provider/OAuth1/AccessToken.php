<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider\OAuth1;

class AccessToken extends \SocialConnect\Auth\OAuth\Token
{
    protected $userId;

    protected $screenName;

    protected $x_auth_expires = 0;

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getScreenName()
    {
        return $this->screenName;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param mixed $screenName
     */
    public function setScreenName($screenName)
    {
        $this->screenName = $screenName;
    }
}
