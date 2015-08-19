<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

use SocialConnect\Auth\Provider\OAuth1\AbstractProvider as OAuth1AbstractProvider;
use SocialConnect\Auth\Provider\OAuth2\AbstractProvider as OAuth2AbstractProvider;
use SocialConnect\Auth\Service;

/**
 * Class Factory
 * @package SocialConnect\Auth\Provider
 */
class CollectionFactory
{
    /**
     * @var array
     */
    protected $collection = [
        'facebook' => '\SocialConnect\Facebook\Provider',
        'github' => '\SocialConnect\Github\Provider',
        'instagram' => '\SocialConnect\Instagram\Provider',
        'twitter' => '\SocialConnect\Twitter\Provider',
        'vk' => '\SocialConnect\Vk\Provider',
    ];

    /**
     * @param array $collection
     */
    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param string $id
     * @param array $parameters
     * @return OAuth1AbstractProvider|OAuth2AbstractProvider
     */
    public function factory($id, array $parameters, Service $service)
    {
        $providerClassName = '\\SocialConnect\\' . $id . '\\Provider';

        $consumer = new Consumer($parameters['applicationId'], $parameters['applicationSecret']);

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
