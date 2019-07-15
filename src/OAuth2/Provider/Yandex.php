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

class Yandex extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'yandex';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://login.yandex.ru/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://oauth.yandex.ru/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://oauth.yandex.ru/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function signRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        $query['format'] = 'json';

        if ($accessToken) {
            $query['oauth_token'] = $accessToken->getToken();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $result = $this->request('GET', 'info', [], $accessToken);

        $hydrator = new ArrayHydrator([
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'default_email' => 'email',
            'real_name' => 'fullname',
            'birthday' => 'birthday',
            'login' => 'username',
        ]);

        return $hydrator->hydrate(new User(), $result);
    }
}
