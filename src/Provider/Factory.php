<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

use SocialConnect\Auth\Service;

/**
 * Class Factory
 * @package SocialConnect\Auth\Provider
 */
class Factory
{
    /**
     * @param string $id
     * @param array $parameters
     * @return OAuth2\Provider
     */
    public static function factory($id, array $parameters, Service $service)
    {
        $providerClassName = '\\SocialConnect\\' . $id . '\\Provider';

        $consumer = new Consumer($parameters['applicationId'], $parameters['applicationSecret']);

        /**
         * @var $provider OAuth2\Provider
         */
        $provider = new $providerClassName($service, $consumer);

        if (isset($parameters['scope'])) {
            $provider->setScope($parameters['scope']);
        }

        return $provider;
    }
}
