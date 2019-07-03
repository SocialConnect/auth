<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use SocialConnect\Common\Http\HeaderValue;

class Cache implements ClientInterface
{
    /**
     * @var ClientInterface
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
     * @param ClientInterface $client
     * @param CacheInterface $cache
     */
    public function __construct(ClientInterface $client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    protected function makeCacheKey(RequestInterface $request): string
    {
        $cacheKey = $request->getUri()->__toString() . $request->getMethod() . $request->getProtocolVersion();

        $headers = $request->getHeaders();
        if ($headers) {
            foreach ($headers as $key => $value) {
                $cacheKey .= $key . '-' . implode(',', $value);
            }
        }

        return md5($cacheKey);
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        if ($method !== 'GET') {
            return $this->client->sendRequest($request);
        }

        $key = $this->makeCacheKey($request);

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $response = $this->client->sendRequest($request);

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
