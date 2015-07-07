<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Instagram;

use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OAuth2\Provider
{
    public function getBaseUri()
    {
        return 'https://api.instagram.com/v1/';
    }

    public function getAuthorizeUri()
    {
        return 'https://api.instagram.com/oauth/authorize';
    }

    public function getRequestTokenUri()
    {
        return 'https://api.instagram.com/oauth/access_token';
    }

    public function getName()
    {
        return 'instagram';
    }

    /**
     * @param $body
     * @return AccessToken
     */
    public function parseToken($body)
    {
        $result = json_decode($body);

        return new AccessToken($result->access_token);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessToken $accessToken)
    {
        $response = $this->service->getHttpClient()->request($this->getBaseUri() . 'users/self?access_token=' . $accessToken->getToken());
        $body = $response->getBody();
        $result = json_decode($body);
        var_dump($result);

        $hydrator = new ObjectMap(array(
            'id' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'email' => 'email'
        ));

        return $hydrator->hydrate(new User(), $result->response[0]);
    }
}
