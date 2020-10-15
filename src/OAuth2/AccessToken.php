<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

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
     * @var string|null
     */
    protected $refreshToken;

    /**
     * @var int|null
     */
    protected $expires;

    /**
     * @var string|null
     */
    protected $uid;

    /**
     * @var string|null
     */
    protected $email;

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
            $this->uid = (string) $token['user_id'];
        }

        if (isset($token['refresh_token'])) {
            $this->refreshToken = $token['refresh_token'];
        }

        if (isset($token['email'])) {
            $this->email = $token['email'];
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
     * @param string $uid
     */
    public function setUserId(string $uid)
    {
        $this->uid = $uid;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string|null
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

    /**
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }
}
