<?php
/**
 * Created by PhpStorm.
 * User: ovr
 * Date: 19.08.15
 * Time: 10:14
 */

namespace SocialConnect\Auth;

use SocialConnect\OAuth1\AbstractProvider as OAuth1AbstractProvider;
use SocialConnect\OAuth2\AbstractProvider as OAuth2AbstractProvider;
use SocialConnect\Auth\Service;

interface FactoryInterface
{
    /**
     * @param string $id
     * @param array $parameters
     * @param Service $service
     * @return OAuth1AbstractProvider|OAuth2AbstractProvider
     */
    public function factory($id, array $parameters, Service $service);

    /**
     * @param string $id
     * @return boolean
     */
    public function has($id);
}
