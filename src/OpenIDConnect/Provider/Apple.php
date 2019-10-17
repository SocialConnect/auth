<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */
declare(strict_types=1);

namespace SocialConnect\OpenIDConnect\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Exception\InvalidArgumentException;
use SocialConnect\Common\Exception\Unsupported;
use SocialConnect\OpenIDConnect\AccessToken;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\OpenIDConnect\AbstractProvider;

/**
 * @link https://developer.apple.com/sign-in-with-apple/get-started/
 */
class Apple extends AbstractProvider
{
    const NAME = 'apple';

    /**
     * {@inheritdoc}
     */
    public function getOpenIdUrl()
    {
        throw new Unsupported('Apple does not support openid-configuration url');
    }

    /**
     * {@inheritDoc}
     */
    public function discover(): array
    {
        return [
            'jwks_uri' => 'https://appleid.apple.com/auth/keys'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://appleid.apple.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://appleid.apple.com/auth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://appleid.apple.com/auth/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * I didnt find
     *
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        if (!$accessToken instanceof AccessToken) {
            throw new InvalidArgumentException(
                '$accessToken must be instance AccessToken'
            );
        }

        $jwt = $accessToken->getJwt();

        $hydrator = new ArrayHydrator([
            'email' => 'email',
            'email_verified' => 'emailVerified '
        ]);

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $jwt->getPayload());

        return $user;
    }
}
