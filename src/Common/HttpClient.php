<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common;

/**
 * Class HttpClient
 * @package SocialConnect\Common
 */
trait HttpClient
{
    /**
     * @var Http\Client\ClientInterface
     */
    protected $httpClient;

    public function setHttpClient(Http\Client\ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return Http\Client\ClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }
}
