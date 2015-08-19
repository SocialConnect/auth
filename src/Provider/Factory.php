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
class Factory implements FactoryInterface
{
    /**
     * @param $id
     * @return string
     */
    protected function buildClassName($id)
    {
        return '\\SocialConnect\\' . $id . '\\Provider';
    }

    /**
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return class_exists($this->buildClassName($id));
    }

    /**
     * @param string $id
     * @param array $parameters
     * @return OAuth1AbstractProvider|OAuth2AbstractProvider
     */
    public function factory($id, array $parameters, Service $service)
    {
        $providerClassName = $this->buildClassName($id);

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
