<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Github;

use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OAuth2\AbstractProvider
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

    public function getName()
    {
        return 'github';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessToken $accessToken)
    {
        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'user',
            [
                'access_token' => $accessToken->getToken()
            ]
        );

        $body = $response->getBody();
        $result = json_decode($body);


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
