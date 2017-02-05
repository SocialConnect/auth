<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Instagram;

use SocialConnect\Auth\Exception\InvalidAccessToken;
use SocialConnect\Auth\Exception\InvalidResponse;
use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OAuth2\AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.instagram.com/v1/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://api.instagram.com/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://api.instagram.com/oauth/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'instagram';
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        $result = json_decode($body);
        if ($result) {
            if (isset($result->access_token) && $result->access_token) {
                return new AccessToken($result->access_token);
            }

            throw new InvalidAccessToken('Instagram response with not valid token');
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
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
            'username' => 'username',
            'bio' => 'bio',
            'website' => 'website',
            'profile_picture' => 'profile_picture',
            'full_name' => 'fullname'
        ));

        return $hydrator->hydrate(new User(), $result->data);
    }
}
