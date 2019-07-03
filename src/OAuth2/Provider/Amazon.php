<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Amazon extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'amazon';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.amazon.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://www.amazon.com/ap/oa';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://api.amazon.com/auth/o2/token';
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
        $response = $this->request('user/profile', [], $accessToken);

        $hydrator = new ObjectMap(
            [
                'user_id' => 'id',
                'name' => 'firstname',
            ]
        );

        return $hydrator->hydrate(new User(), $response);
    }
}
