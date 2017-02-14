<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth;

use Exception;
use Psr\Cache\CacheItemPoolInterface;
use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\Provider\Session\SessionInterface;

class Service
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @param ClientInterface $httpClient
     * @param SessionInterface $session
     * @param array $config
     * @param CacheItemPoolInterface|null $cache
     * @param FactoryInterface|null $factory
     */
    public function __construct(ClientInterface $httpClient, SessionInterface $session, array $config, CacheItemPoolInterface $cache = null, FactoryInterface $factory = null)
    {
        $this->httpClient = $httpClient;
        $this->session = $session;
        $this->config = $config;

        $this->cache = $cache;
        $this->factory = is_null($factory) ? new CollectionFactory() : $factory;
    }

    /**
     * @param $name
     * @return array
     * @throws Exception
     */
    public function getProviderConfiguration($name)
    {
        if (isset($this->config['provider'][$name])) {
            return $this->config['provider'][$name];
        }

        throw new Exception('Please setup configuration for ' . ucfirst($name) . ' provider');
    }

    /**
     * Get provider class by $name
     *
     * @param $name
     * @return \SocialConnect\Provider\AbstractBaseProvider
     * @throws Exception
     */
    public function getProvider($name)
    {
        return $this->factory->factory($name, $this->getProviderConfiguration($name), $this);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return ClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCache()
    {
        return $this->cache;
    }
}
