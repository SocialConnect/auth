<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth;

/**
 * Class Service
 * @package SocialConnect\Auth
 */
class Service
{
    use \SocialConnect\Common\HttpClient;

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
    public function __construct(array $config, $storage)
    {
        $this->config = $config;
        $this->storage = $storage;
    }

    /**
     * @param $name
     * @return array
     * @throws \Exception
     */
    public function getProviderConfiguration($name)
    {
        if (isset($this->config['provider'][ucfirst($name)])) {
            return $this->config['provider'][ucfirst($name)];
        }

        throw new \Exception('Please setup configuration for ' . ucfirst($name) . ' provider');
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function getProvider($name)
    {
        return Provider\Factory::factory(ucfirst($name), $this->getProviderConfiguration($name));
    }
}
