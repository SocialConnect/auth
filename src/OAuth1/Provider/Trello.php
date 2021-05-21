<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth1\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\OAuth1\AbstractProvider;
use SocialConnect\Common\Entity\User;

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
        $parameters = [
            'key' => $this->consumer->getKey(),
            'token' => $accessToken->getToken()
        ];

        $result = $this->request(
            'GET',
            'members/me',
            $parameters
        );

        $hydrator = new ArrayHydrator([
            'avatarUrl' => 'pictureURL',
            'fullName' => 'fullname',
        ]);

        return $hydrator->hydrate(new User(), $result);
    }
}
