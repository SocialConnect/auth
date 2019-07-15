<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Common\Entity\User;

class Bitbucket extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'bitbucket';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.bitbucket.org/2.0/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://bitbucket.org/site/oauth2/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://bitbucket.org/site/oauth2/access_token';
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
        $response = $this->request('GET', 'user', [], $accessToken);

        $hydrator = new ArrayHydrator([
            'uuid' => 'id',
            'display_name' => 'fullname',
        ]);

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $response);
        $user->pictureURL = "https://bitbucket.org/account/{$user->username}/avatar/512/";

        return $user;
    }
}
