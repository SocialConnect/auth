<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Github;

use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

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

    /**
     * @return string
     */
    public function makeAuthUrl()
    {
        return $this->getAuthorizeUri() . '?' . http_build_query(array(
            'client_id' => $this->applicationId,
            'redirect_uri' => $this->getRedirectUrl()
        ));
    }

    /**
     * @param $code
     * @return AccessToken
     */
    public function getAccessToken($code)
    {
        $parameters = array(
            'client_id' => $this->applicationId,
            'client_secret' => $this->applicationSecret,
            'code' => $code,
            'redirect_uri' => $this->getRedirectUrl()
        );

        $response = $this->service->getHttpClient()->request($this->getRequestTokenUri() . '?' . http_build_query($parameters));
        $body = $response->getBody();

        parse_str($body, $token);

        return new AccessToken($token['access_token']);
    }


    public function getUser(AccessToken $accessToken)
    {
        $response = $this->service->getHttpClient()->request($this->getBaseUri() . 'user?access_token=' . $accessToken->getToken());
        $body = $response->getBody();
        $result = \GuzzleHttp\json_decode($body);


        $hydrator = new ObjectMap(array(
            'id' => 'id',
            'url' => 'api_url',
            'html_url' => 'url',
            'followers_url' => 'followers_url',
            'following_url' => 'following_url',
            'gists_url' => 'gists_url',
            'starred_url' => 'starred_url',
            'subscriptions_url' => 'subscriptions_url',
            'organizations_url' => 'organizations_url',
            'repos_url' => 'repos_url',
            'events_url' => 'events_url',
            'received_events_url' => 'received_events_url',
            'type' => 'type',
            'site_admin' => 'site_admin',
            'name' => 'name',
            'company' => 'company',
            'blog' => 'blog',
            'location' => 'location',
            'login' => 'login',
            'avatar_url' => 'avatar_url',
            'gravatar_id' => 'gravatar_id',
            'hireable' => 'hireable',
            'bio' => 'bio',
            'public_repos' => 'public_repos',
            'public_gists' => 'public_gists',
            'followers' => 'followers',
            'following' => 'following',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'email' => 'email',
        ));

        return $hydrator->hydrate(new User(), $result);
    }
}
