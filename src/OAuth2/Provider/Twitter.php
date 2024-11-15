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

class Twitter extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'twitter';

    public function getBaseUri()
    {
        return 'https://api.x.com/2/';
    }

    public function getAuthorizeUri()
    {
        return 'https://api.x.com/2/oauth2';
    }

    public function getRequestTokenUri()
    {
        return 'https://api.x.com/2/oauth2/token';
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $query = [];

        $fields = $this->getArrayOption('identity.fields', []);
        if ($fields) {
            $query['fields'] = implode(',', $fields);
        }

        $response = $this->request(
            'GET',
            'me',
            $query,
            $accessToken
        );

        $hydrator = new ArrayHydrator([
            'id' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'email' => 'email',
            'gender' => static function ($value, User $user) {
                $user->setSex($value === 1 ? User::SEX_FEMALE : User::SEX_MALE);
            },
            'birthday' => static function ($value, User $user) {
                $user->setBirthday(
                    new \DateTime($value)
                );
            },
            'link' => 'url',
            'locale' => 'locale',
            'name' => 'fullname',
            'timezone' => 'timezone',
            'updated_time' => 'dateModified',
            'verified' => 'verified',
            'picture.data.url' => 'pictureURL'
        ]);

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $response);
        $user->emailVerified = true;

        return $user;
    }
}
