<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Vk;

use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

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

    public function getName()
    {
        return 'vk';
    }

    /**
     * @param $body
     * @return AccessToken
     */
    public function parseToken($body)
    {
        $result = \json_decode($body);

        return new AccessToken($result->access_token);
    }

    /**
     * @param AccessToken $accessToken
     * @return User
     */
    public function getUser(AccessToken $accessToken)
    {
        $response = $this->service->getHttpClient()->request($this->getBaseUri() . '/method/users.get?v=5.24&access_token=' . $accessToken->getToken());
        $body = $response->getBody();
        $result = \json_decode($body);

        $hydrator = new ObjectMap(array(
            'id' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname'
        ));

        return $hydrator->hydrate(new User(), $result->response[0]);
    }
}
