<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Twitter;

use SocialConnect\Auth\Provider\OAuth1\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OAuth1\Provider
{
    public function getBaseUri()
    {
        return 'https://api.twitter.com/1.1/';
    }

    public function getAuthorizeUri()
    {
        return 'https://api.twitter.com/oauth/authenticate';
    }

    public function getRequestTokenUrl()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    public function getRequestTokenAccessUrl()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    public function getName()
    {
        return 'twitter';
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
//        $response = $this->service->getHttpClient()->request($this->getBaseUri() . 'method/users.get?v=5.24&access_token=' . $accessToken->getToken());
//        $body = $response->getBody();
//        $result = json_decode($body);
//
//        $hydrator = new ObjectMap(array(
//            'id' => 'id',
//            'first_name' => 'firstname',
//            'last_name' => 'lastname',
//            'email' => 'email'
//        ));
//
//        return $hydrator->hydrate(new User(), $result->response[0]);
    }
}
