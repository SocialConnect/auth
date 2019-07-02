<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common;

use InvalidArgumentException;

/**
 * Class ClientAbstract
 * @package SocialConnect\Common
 */
abstract class ClientAbstract
{
    /**
     * Application secret
     *
     * @var string|integer
     */
    protected $appId;

    /**
     * Application secret
     *
     * @var string
     */
    protected $appSecret;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @param int $appId
     * @param string $appSecret
     * @param null $accessToken
     * @throws InvalidArgumentException
     */
    public function __construct($appId, $appSecret, $accessToken = null)
    {
        if (empty($appId)) {
            throw new InvalidArgumentException('$appId cannot be empty');
        }
        $this->appId = $appId;

        if (!is_string($appSecret)) {
            throw new InvalidArgumentException('$appSecret must be string');
        }
        $this->appSecret = $appSecret;

        if ($accessToken) {
            $this->setAccessToken($accessToken);
        }
    }

    /**
     * @return string
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @return int|string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    abstract public function setAccessToken($accessToken);
}
