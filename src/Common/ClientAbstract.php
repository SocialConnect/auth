<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Common;

use SocialConnect\Provider\Consumer;

abstract class ClientAbstract
{
    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var HttpStack
     */
    protected $httpStack;

    /**
     * @param HttpStack $httpStack
     * @param Consumer $consumer
     * @param string|null $accessToken
     */
    public function __construct(HttpStack $httpStack, Consumer $consumer, string $accessToken = null)
    {
        $this->consumer = $consumer;
        $this->httpStack = $httpStack;

        if ($accessToken) {
            $this->setAccessToken($accessToken);
        }
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
