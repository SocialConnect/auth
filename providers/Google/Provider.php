<?php

/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */

namespace SocialConnect\Google;

use SocialConnect\Auth\Provider\OAuth2\AbstractProvider;
use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Common\Hydrator\ObjectMap;
use SocialConnect\Auth\Exception\InvalidAccessToken;

/**
 * Class Provider
 * @package SocialConnect\Google
 */
class Provider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $requestHttpMethod = Client::GET;

    /**
     * @return string
     */
    public function getAuthorizeUri()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'google';
    }

    /**
     * @return array
     */
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
     * @throws \InvalidArgumentException
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
     * @return string
     */
    public function getRequestTokenUri()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    /**
     * @param string $body
     * @return AccessToken
     * @throws InvalidAccessToken
     */
    public function parseToken($body)
    {
        $result = json_decode($body);

        if (!isset($result->access_token) || empty($result->access_token)) {
            throw new InvalidAccessToken;
        }

        return new AccessToken($result->access_token);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessToken $accessToken)
    {
        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'oauth2/v1/userinfo',
            ['access_token' => $accessToken->getToken()]
        );

        $body = $response->getBody();
        $result = json_decode($body);

        $hydrator = new ObjectMap(array(
            'id' => 'id',
            'given_name' => 'firstname',
            'family_name' => 'lastname',
            'email' => 'email'
        ));

        return $hydrator->hydrate(new User(), $result);
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return 'https://www.googleapis.com/';
    }
}