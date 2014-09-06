<?php

/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
class Factory
{
    static public function factory($id, array $parameters)
    {
        $providerClassName = '\\SocialConnect\\' . $id . '\\Provider';

        return new $providerClassName();
    }
}
