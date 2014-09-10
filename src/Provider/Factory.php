<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

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
    static public function factory($id, array $parameters)
    {
        $providerClassName = '\\SocialConnect\\' . $id . '\\Provider';

        return new $providerClassName();
    }
}
