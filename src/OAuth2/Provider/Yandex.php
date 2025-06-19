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
    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, ?AccessTokenInterface $accessToken = null): void
    {
        $query['format'] = 'json';

        if ($accessToken) {
            $headers['Authorization'] = "OAuth {$accessToken->getToken()}";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $result = $this->request('GET', 'info', [], $accessToken);

        $hydrator = new ArrayHydrator([
            'id' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'default_email' => 'email',
            'real_name' => 'fullname',
            'sex' => static function ($value, User $user) {
                $user->setSex($value === 'male' ?  User::SEX_MALE : User::SEX_FEMALE);
            },
            'birthday' => static function ($value, User $user) {
                $user->setBirthday(
                    new \DateTime($value)
                );
            },
            'login' => 'username',
            'default_avatar_id' => static function ($value, User $user) {
                $user->pictureURL = 'https://avatars.yandex.net/get-yapic/'.$value.'/islands-200';
            }
        ]);

        return $hydrator->hydrate(new User(), $result);
    }
}
