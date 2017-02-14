<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Provider;

/**
 * This interface indicate that Provider allow US to use cache for it
 *
 * Mostly for OpenID and OpenIDConnect
 * There are discover specification for this provider, that should be cached
 * to speed up authorization process, because request every time this spec is not a best solution
 */
interface CacheUsageInterface
{
    /**
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     */
    public function setCache(\Psr\Cache\CacheItemPoolInterface $cache);
}
