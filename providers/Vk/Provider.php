<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Vk;

class Provider extends \SocialConnect\Auth\Provider\OAuth2\Provider
{
    public function getBaseUri()
    {
        return 'https://api.vk.com/';
    }

    public function getAuthorizeUri()
    {
        return 'http://api.vk.com/oauth/authorize';
    }

    public function getRequestTokenUri()
    {
        return 'https://api.vk.com/oauth/token';
    }
}
