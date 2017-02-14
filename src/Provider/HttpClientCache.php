<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */


namespace SocialConnect\Provider;

use Cache\Adapter\Common\CacheItem;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use SocialConnect\Common\Http\Client\Cache;
use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\Common\Http\Request;

class CacheItem implements CacheItemInterface
{
    protected $key;

    protected $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }

    public function isHit()
    {
        return false;
    }

    public function set($value)
    {
        $this->value = $value;
    }


    public function expiresAt($expiration)
    {

    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|\DateInterval|null $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter($time)
    {
        return false;
    }
}

class HttpClientCache
{
    /**
     * @param ClientInterface $client
     * @param Request $request
     * @param CacheItemPoolInterface|null $cache
     * @return \SocialConnect\Common\Http\Response
     */
    public static function cacheResponse(ClientInterface $client, Request $request, CacheItemPoolInterface $cache = null)
    {
        if (!$cache) {
            return $client->fromRequest($request);
        }

        $cacheKey = 'sc-auth-' . md5($request->getUri() . '?' . implode('&', $request->getParameters()));

        $cacheItem = $cache->getItem($cacheKey);
        $value = $cacheItem->get();

        if ($value) {
            return $value;
        }


        $response = $client->fromRequest($request);

        $cache->save(
            new Cache($response)
        );

        
    }
}
