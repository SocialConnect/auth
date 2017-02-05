<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1;

class Token
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * Generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "oauth_token=%s&oauth_token_secret=%s",
            Util::urldecodeRFC3986($this->key),
            Util::urldecodeRFC3986($this->secret)
        );
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }
}
