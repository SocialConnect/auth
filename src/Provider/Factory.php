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
    static public function factory($id, array $parameters)
    {
        $providerClassName = '\\SocialConnect\\' . $id . '\\Provider';

        return new $providerClassName();
    }
}
