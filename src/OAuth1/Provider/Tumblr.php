<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth1\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth1\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Tumblr extends AbstractProvider
{
    const NAME = 'tumblr';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.tumblr.com/v2/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://www.tumblr.com/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://www.tumblr.com/oauth/request_token';
    }

    /**
     * @return string
     */
    public function getRequestTokenAccessUri()
    {
        return 'https://www.tumblr.com/oauth/access_token';
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $this->consumerToken = $accessToken;

        $parameters = [
            'oauth_consumer_key' => $this->consumer->getKey(),
            'oauth_token' => $accessToken->getToken()
        ];

        $response = $this->oauthRequest(
            $this->getBaseUri() . 'user/info',
            'GET',
            $parameters
        );

        $result = $this->hydrateResponse($response);

        if (!isset($result->response, $result->response->user) || !$result->response->user) {
            throw new InvalidResponse(
                'API response without user inside JSON',
                $response
            );
        }

        $hydrator = new ObjectMap(
            [
                'name' => 'id'
            ]
        );

        return $hydrator->hydrate(new User(), $result->response->user);
    }
}
