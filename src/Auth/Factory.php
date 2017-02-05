<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth;

use SocialConnect\OAuth1\AbstractProvider as OAuth1AbstractProvider;
use SocialConnect\OAuth2\AbstractProvider as OAuth2AbstractProvider;

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
     * @param Service $service
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

        if (isset($parameters['fields'])) {
            $provider->setFields($parameters['fields']);
        }

        return $provider;
    }
}
