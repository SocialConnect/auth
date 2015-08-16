<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth;

use Exception;
use SocialConnect\Common\HttpClient;

/**
 * Class Service
 * @package SocialConnect\Auth
 */
class Service
{
    use HttpClient;

    /**
     * @var
     */
    protected $storage;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     * @param $storage
     */
    public function __construct(array $config, $storage = null)
    {
        $this->config = $config;
        $this->storage = $storage;
    }

    /**
     * @param $name
     * @return array
     * @throws Exception
     */
    public function getProviderConfiguration($name)
    {
        if (isset($this->config['provider'][ucfirst($name)])) {
            return $this->config['provider'][ucfirst($name)];
        }

        throw new Exception('Please setup configuration for ' . ucfirst($name) . ' provider');
    }

    /**
     * Get provider class by $name
     *
     * @param $name
     * @return Provider\OAuth1\AbstractProvider|Provider\OAuth2\AbstractProvider
     * @throws Exception
     */
    public function getProvider($name)
    {
        return Provider\Factory::factory(ucfirst($name), $this->getProviderConfiguration($name), $this);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
