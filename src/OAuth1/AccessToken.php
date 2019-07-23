<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth1;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;

class AccessToken extends \SocialConnect\OAuth1\Token implements AccessTokenInterface
{
    /**
     * @var string|null
     */
    protected $userId;

    /**
     * @var string
     */
    protected $screenName;

    public function __construct(array $token)
    {
        if (!isset($token['oauth_token'])) {
            throw new InvalidAccessToken(
                'API returned data without oauth_token field'
            );
        }

        if (!isset($token['oauth_token_secret'])) {
            throw new InvalidAccessToken(
                'API returned data without oauth_token_secret field'
            );
        }

        parent::__construct($token['oauth_token'], $token['oauth_token_secret']);

        if (isset($token['user_id'])) {
            $this->userId = (string) $token['user_id'];
        }

        if (isset($token['screen_name'])) {
            $this->screenName = $token['screen_name'];
        }
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string|null
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
