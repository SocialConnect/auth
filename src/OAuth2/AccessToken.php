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

        // Show preference to 'expires_in' since it is defined in RFC6749 Section 5.1.
        // Defer to 'expires' if it is provided instead.
        if (isset($token['expires_in'])) {
            if (!is_numeric($token['expires_in'])) {
                throw new InvalidAccessToken('expires_in value must be an integer');
            }

            $this->expires = $token['expires_in'] != 0 ? time() + $token['expires_in'] : 0;
        } elseif (!empty($token['expires'])) {
            // Some providers supply the seconds until expiration rather than
            // the exact timestamp. Take a best guess at which we received.
            $expires = $token['expires'];
            if (!$this->isExpirationTimestamp($expires)) {
                $expires += time();
            }

            $this->expires = $expires;
        }

        if (isset($token['user_id'])) {
            $this->uid = $token['user_id'];
        }
    }

    /**
     * Check if a value is an expiration timestamp or second value.
     *
     * @param integer $value
     * @return bool
     */
    protected function isExpirationTimestamp($value)
    {
        // If the given value is larger than the original OAuth 2 draft date,
        // assume that it is meant to be a (possible expired) timestamp.
        $oauth2InceptionDate = 1349067600; // 2012-10-01
        return ($value > $oauth2InceptionDate);
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
