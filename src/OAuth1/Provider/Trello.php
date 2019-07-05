<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth1\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\OAuth1\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Trello extends AbstractProvider
{
    const NAME = 'trello';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.trello.com/1/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://trello.com/1/OAuthAuthorizeToken';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://trello.com/1/OAuthGetRequestToken';
    }

    /**
     * @return string
     */
    public function getRequestTokenAccessUri()
    {
        return 'https://trello.com/1/OAuthGetAccessToken';
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
            'key' => $this->consumer->getKey(),
            'token' => $accessToken->getToken()
        ];

        $response = $this->oauthRequest(
            $this->getBaseUri() . 'members/me',
            'GET',
            $parameters
        );

        $result = $this->hydrateResponse($response);

        $hydrator = new ObjectMap([
            'avatarUrl' => 'pictureURL',
            'fullName' => 'fullname',
        ]);

        return $hydrator->hydrate(new User(), $result);
    }
}
