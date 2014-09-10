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
     * @param $id
     * @param array $parameters
     * @return OAuth2\Provider
     */
    static public function factory($id, array $parameters, Service $service)
    {
        $providerClassName = '\\SocialConnect\\' . $id . '\\Provider';

        $provider = new $providerClassName($service);

        if (isset($parameters['applicationId'])) {
            $provider->setApplicationId($parameters['applicationId']);
        }

        return $provider;
    }
}
