<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */
declare(strict_types=1);

namespace SocialConnect\OpenIDConnect\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Common\Exception\InvalidArgumentException;
use SocialConnect\OpenIDConnect\AccessToken;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\OpenIDConnect\AbstractProvider;
use SocialConnect\Common\Entity\User;

class Google extends AbstractProvider
{
    const NAME = 'google';

    /**
     * {@inheritdoc}
     */
    public function getOpenIdUrl()
    {
        return 'https://accounts.google.com/.well-known/openid-configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://www.googleapis.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://accounts.google.com/o/oauth2/token';
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
    public function extractIdentity(AccessTokenInterface $accessToken)
    {
        if (!$accessToken instanceof AccessToken) {
            throw new InvalidArgumentException(
                '$accessToken must be instance AccessToken'
            );
        }

        $jwt = $accessToken->getJwt();

        $hydrator = new ArrayHydrator([
            'sub' => 'id',
            'email' => 'email',
            'email_verified' => 'emailVerified',
            'name' => 'fullname',
            'picture' => 'pictureURL',
            'given_name' => 'firstname',
            'family_name' => 'lastname',
        ]);

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $jwt->getPayload());

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('GET', 'oauth2/v1/userinfo', [], $accessToken);

        $hydrator = new ArrayHydrator([
            'id' => 'id',
            'given_name' => 'firstname',
            'family_name' => 'lastname',
            'email' => 'email',
            'verified_email' => 'emailVerified',
            'name' => 'fullname',
            'picture' => 'pictureURL',
            'gender' => static function ($value, User $user) {
                $user->setSex($value);
            },
        ]);

        return $hydrator->hydrate(new User(), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeInline()
    {
        return implode(' ', $this->scope);
    }
}
