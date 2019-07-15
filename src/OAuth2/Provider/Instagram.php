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

class Instagram extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'instagram';

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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('GET', 'users/self', [], $accessToken);

        $hydrator = new ArrayHydrator([
            'id' => 'id',
            'username' => 'username',
            'bio' => 'bio',
            'website' => 'website',
            'profile_picture' => 'pictureURL',
            'full_name' => 'fullname'
        ]);

        return $hydrator->hydrate(new User(), $response['data']);
    }
}
