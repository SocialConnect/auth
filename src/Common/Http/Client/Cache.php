<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client;

use Psr\SimpleCache\CacheInterface;
use SocialConnect\Common\Http\HeaderValue;
use SocialConnect\Common\Http\Response;

class Cache extends Client
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var int[]
     */
    protected $statusAccepted = [
        200 => 200,
        203 => 203,
        204 => 204,
        300 => 300,
        301 => 301,
        404 => 404,
        405 => 405,
        410 => 410,
        414 => 414,
        418 => 418,
        501 => 501,
    ];

    /**
     * @param Client $client
     * @param CacheInterface $cache
     */
    public function __construct(Client $client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    /**
     * @param string $url
     * @param array $parameters
     * @return string
     */
    protected function makeCacheKey(string $url, array $parameters = array()): string
    {
        $cacheKey = $url;

        if ($parameters) {
            foreach ($parameters as $key => $value) {
                $cacheKey .= $key . '-' . $value;
            }
        }

        return md5($cacheKey);
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $url, array $options = [], array $headers = [], string $method = Client::GET): Response
    {
        if ($method != Client::GET) {
            return $this->client->request($url, $options, $headers, $method);
        }

        $key = $this->makeCacheKey($url, $options);

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $response = $this->client->request($url, $options, $headers, $method);

        if (!isset($this->statusAccepted[$response->getStatusCode()])) {
            return $response;
        }

        $cacheControl = new HeaderValue($response->getHeader('Cache-Control'));
        if ($cacheControl->has('no-store') || $cacheControl->has('no-cache')) {
            return $response;
        }

        if ($cacheControl->has('max-age')) {
            $maxAge = $cacheControl->get('max-age');
            if (is_numeric($maxAge)) {
                $this->cache->set($key, $response, (int) $maxAge);
            }

            return $response;
        }

        $noCache = $response->hasHeader('Pragma') && $response->getHeader('Pragma') == 'no-cache';

        if (!$noCache && $response->hasHeader('Expires')) {
            // @link https://tools.ietf.org/html/rfc7234#section-5.3
            $expires = \DateTime::createFromFormat(\DateTime::RFC1123, $response->getHeader('Expires'));
            if ($expires !== false) {
                $lifeTime = $expires->getTimestamp() - time();
                if ($lifeTime > 0) {
                    $this->cache->set($key, $response, $lifeTime);
                }
            }
        }

        return $response;
    }
}
