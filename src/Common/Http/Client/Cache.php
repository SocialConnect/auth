<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client;

class Cache extends Client
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $cache;

    /**
     * @param Client $client
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct(Client $client, \Doctrine\Common\Cache\Cache $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    /**
     * @param string $url
     * @param array $parameters
     * @return string|null
     */
    protected function makeCacheKey($url, array $parameters = array())
    {
        $cacheKey = $url;

        if ($parameters) {
            foreach ($parameters as $key => $value) {
                $cacheKey .= $key . '-' . $value;
            }
        }

        return 'sc:' . md5($cacheKey);
    }

    /**
     * {@inheritdoc}
     */
    public function request($url, array $parameters = array(), $method = Client::GET, array $headers = array(), array $options = array())
    {
        if ($method != Client::GET) {
            return $this->client->request($url, $parameters, $method, $headers, $options);
        }

        $key = $this->makeCacheKey($url, $parameters);
        if ($key && $this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        $response = $this->client->request($url, $parameters, $method, $headers, $options);
        $noCache = $response->hasHeader('Pragma') && $response->getHeader('Pragma') == 'no-cache';

        if (!$noCache && $response->hasHeader('Expires')) {
            $expires = new \DateTime($response->getHeader('Expires'));
            $lifeTime = $expires->getTimestamp() - time() - 60;

            if ($key) {
                $this->cache->save($key, $response, $lifeTime);
            }
        }

        return $response;
    }
}
