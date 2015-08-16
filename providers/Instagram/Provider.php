<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Instagram;

use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OAuth2\AbstractProvider
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

    public function getAuthUrlParameters()
    {
        return array(
            'client_id' => $this->consumer->getKey(),
            'redirect_uri' => $this->getRedirectUrl(),
            'response_type' => 'code'
        );
    }

    /**
     * @param string $code
     * @return AccessToken
     */
    public function getAccessToken($code)
    {
        if (!is_string($code)) {
            throw new \InvalidArgumentException('Parameter $code must be a string');
        }

        $parameters = array(
            'client_id' => $this->consumer->getKey(),
            'client_secret' => $this->consumer->getSecret(),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getRedirectUrl()
        );

        $response = $this->service->getHttpClient()->request($this->getRequestTokenUri(), $parameters, Client::POST);
        $body = $response->getBody();

        return $this->parseToken($body);
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
        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'users/self',
            [
                'access_token' => $accessToken->getToken()
            ]
        );

        $body = $response->getBody();
        $result = json_decode($body);

        $hydrator = new ObjectMap(array(
            'id' => 'id',
            'username' => 'username',
            'bio' => 'bio',
            'website' => 'website',
            'profile_picture' => 'profile_picture',
            'full_name' => 'full_name'
        ));

        return $hydrator->hydrate(new User(), $result->data);
    }
}
