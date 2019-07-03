<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class DigitalOcean extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'digital-ocean';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.digitalocean.com/v2/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://cloud.digitalocean.com/v1/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://cloud.digitalocean.com/v1/oauth/token';
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
        $response = $this->request('account', [], $accessToken);

        $hydrator = new ObjectMap(
            [
                'uuid' => 'id',
            ]
        );

        return $hydrator->hydrate(new User(), $response->account);
    }
}
