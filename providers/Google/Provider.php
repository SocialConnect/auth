<?php

/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */

namespace SocialConnect\Google;

use SocialConnect\Auth\Exception\InvalidResponse;
use SocialConnect\Auth\Provider\OAuth2\AbstractProvider;
use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;
use SocialConnect\Auth\Exception\InvalidAccessToken;

/**
 * Class Provider
 * @package SocialConnect\Google
 */
class Provider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://www.googleapis.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'google';
    }

    /**
     * {@inheritdoc}
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
            [
                'access_token' => $accessToken->getToken()
            ]
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $body = $response->getBody();
        $result = json_decode($body);

        $hydrator = new ObjectMap(array(
            'id' => 'id',
            'given_name' => 'firstname',
            'family_name' => 'lastname',
            'email' => 'email',
            'name' => 'fullname',
            'gender' => 'sex',
        ));

        return $hydrator->hydrate(new User(), $result);
    }
}
