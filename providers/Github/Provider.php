<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Github;

class Provider extends \SocialConnect\Auth\Provider\OAuth2\Provider
{
    public function getBaseUri()
    {
        return 'https://api.github.com/';
    }

    public function getAuthorizeUri()
    {
        return 'https://github.com/login/oauth/authorize';
    }

    public function getRequestTokenUri()
    {
        return 'https://github.com/login/oauth/access_token';
    }

    public function getRedirectUrl()
    {
        return $this->getRedirectUri() . '?provider=github';
    }

    public function makeAuthUrl()
    {
        return $this->getAuthorizeUri() . '?' . http_build_query(array(
            'client_id' => $this->applicationId,
            'redirect_uri' => $this->getRedirectUrl()
        ));
    }

    public function getAccessToken($code)
    {
        $url = $this->getRedirectUri();

        $parameters = array(
            'client_id' => $this->applicationId,
            'client_secret' => $this->applicationSecret,
            'code' => $code,
            'redirect_uri' => $this->getRedirectUrl()
        );
    }
}
