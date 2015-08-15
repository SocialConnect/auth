<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider\OAuth1;

class AccessToken extends \SocialConnect\Auth\OAuth\Token
{
    /**
     * @var integer
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
     * @param $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param $screenName
     */
    public function setScreenName($screenName)
    {
        $this->screenName = $screenName;
    }
}
