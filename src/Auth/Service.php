<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Auth;

use Exception;
use SocialConnect\Provider\HttpStack;
use SocialConnect\Provider\Session\SessionInterface;

class Service
{
    /**
     * @var HttpStack
     */
    protected $httpStack;

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
     * @param HttpStack $httpStack
     * @param SessionInterface $session
     * @param array $config
     * @param FactoryInterface|null $factory
     * @internal param $storage
     */
    public function __construct(HttpStack $httpStack, SessionInterface $session, array $config, FactoryInterface $factory = null)
    {
        $this->httpStack = $httpStack;
        $this->session = $session;
        $this->config = $config;

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
     * Check that provider exists by $name
     *
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return $this->factory->has($name);
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
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return HttpStack
     */
    public function getHttpStack(): HttpStack
    {
        return $this->httpStack;
    }
}
