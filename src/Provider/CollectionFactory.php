<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

use LogicException;
use SocialConnect\Auth\Provider\OAuth1\AbstractProvider as OAuth1AbstractProvider;
use SocialConnect\Auth\Provider\OAuth2\AbstractProvider as OAuth2AbstractProvider;
use SocialConnect\Auth\Service;

/**
 * Class Factory
 * @package SocialConnect\Auth\Provider
 */
class CollectionFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $providers = [
        'facebook' => '\SocialConnect\Facebook\Provider',
        'github' => '\SocialConnect\Github\Provider',
        'instagram' => '\SocialConnect\Instagram\Provider',
        'twitter' => '\SocialConnect\Twitter\Provider',
        'vk' => '\SocialConnect\Vk\Provider',
    ];

    /**
     * @param array $collection
     */
    public function __construct(array $providers = null)
    {
        if ($providers) {
            $this->providers = $providers;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this->providers[$id]);
    }

    /**
     * @param string $id
     * @param array $parameters
     * @return OAuth1AbstractProvider|OAuth2AbstractProvider
     */
    public function factory($id, array $parameters, Service $service)
    {
        $consumer = new Consumer($parameters['applicationId'], $parameters['applicationSecret']);

        $id = strtolower($id);

        if (!isset($this->providers[$id])) {
            throw new LogicException('Provider with $id = ' . $id . ' doest not exist');
        }

        $providerClassName = $this->providers[$id];

        /**
         * @var $provider AbstractBaseProvider
         */
        $provider = new $providerClassName($service, $consumer);

        if (isset($parameters['scope'])) {
            $provider->setScope($parameters['scope']);
        }

        return $provider;
    }
}
