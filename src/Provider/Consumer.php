<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Provider;

class Consumer
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
     * Some API's need another key then secret for API requests
     *
     * @var string
     */
    protected $public;

    /**
     * @var string|null
     */
    public $callback_url;

    /**
     * @param string $key
     * @param string $secret
     * @param string|null $callback_url
     */
    public function __construct($key, $secret, $callback_url = null)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->callback_url = $callback_url;
    }

    public function __toString()
    {
        return "OAuthConsumer[key=$this->key,secret=$this->secret]";
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

    /**
     * @return string
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * @param string $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }
}
