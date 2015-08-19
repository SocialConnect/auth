<?php
/**
 * Created by PhpStorm.
 * User: ovr
 * Date: 19.08.15
 * Time: 10:14
 */

namespace SocialConnect\Auth\Provider;

use SocialConnect\Auth\Provider\OAuth1\AbstractProvider as OAuth1AbstractProvider;
use SocialConnect\Auth\Provider\OAuth2\AbstractProvider as OAuth2AbstractProvider;
use SocialConnect\Auth\Service;

interface FactoryInterface
{
    /**
     * @param string $id
     * @param array $parameters
     * @return OAuth1AbstractProvider|OAuth2AbstractProvider
     */
    public function factory($id, array $parameters, Service $service);

    /**
     * @param string $id
     * @return boolean
     */
    public function has($id);
}
