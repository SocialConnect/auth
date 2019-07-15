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

class Discord extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'discord';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://discordapp.com/api/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://discordapp.com/api/oauth2/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://discordapp.com/api/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getScopeInline()
    {
        return implode(' ', $this->scope);
    }

    /**
     * {@inheritDoc}
     */
    public function signRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken->getToken()}";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('GET', 'users/@me', [], $accessToken);

        $hydrator = new ArrayHydrator([
            'verified' => 'emailVerified'
        ]);

        return $hydrator->hydrate(new User(), $response);
    }
}
