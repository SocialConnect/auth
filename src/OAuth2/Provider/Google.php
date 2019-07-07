<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\OAuth2\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Google extends AbstractProvider
{
    const NAME = 'google';

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
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $query = [];

        $fields = $this->getArrayOption('identity.fields', [
            'id',
            'email',
            'verified_email',
            'name',
            'given_name',
            'family_name',
            'picture',
            'locale',
            //
            'gender',
            'hd',
            'link',
        ]);
        if ($fields) {
            $query['fields'] = implode(',', $fields);
        }

        $response = $this->request('oauth2/v1/userinfo', $query, $accessToken);

        $hydrator = new ObjectMap(
            [
                'id' => 'id',
                'given_name' => 'firstname',
                'family_name' => 'lastname',
                'email' => 'email',
                'verified_email' => 'emailVerified',
                'name' => 'fullname',
                'gender' => 'sex',
                'picture' => 'pictureURL'
            ]
        );

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
