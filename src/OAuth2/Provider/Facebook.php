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

class Facebook extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'facebook';

    public function getBaseUri()
    {
        return 'https://graph.facebook.com/v3.3/';
    }

    public function getAuthorizeUri()
    {
        return 'https://www.facebook.com/dialog/oauth';
    }

    public function getRequestTokenUri()
    {
        return 'https://graph.facebook.com/oauth/access_token';
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
                switch ($value) {
                    case 'male':
                        $gender = User::GENDER_MALE;
                        break;
                    case 'female':
                        $gender = User::GENDER_FEMALE;
                        break;
                    default:
                        $gender = User::GENDER_OTHER;
                        break;
                }
                $user->setGender($gender);
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
