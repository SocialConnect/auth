<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common;

use Psr\Http\Client\ClientInterface;

trait HttpClient
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return ClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }
}
