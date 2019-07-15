<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */
declare(strict_types=1);

namespace SocialConnect\OpenIDConnect\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\OpenIDConnect\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class PixelPin extends AbstractProvider
{
    const NAME = 'pixelpin';

    /**
     * {@inheritdoc}
     */
    public function getOpenIdUrl()
    {
        return 'https://login.pixelpin.io/.well-known/openid-configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://login.pixelpin.io/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://login.pixelpin.io/connect/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://login.pixelpin.io/connect/token';
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
        $response = $this->request('GET', 'connect/userinfo', [], $accessToken);

        $hydrator = new ObjectMap(
            [
                'sub' => 'id',
                'given_name' => 'firstname',
                'family_name' => 'lastname',
                'email' => 'email',
                'display_name' => 'fullname',
                'gender' => 'gender',
                'phone_number' => 'phone',
                'birthdate' => 'birthdate',
                'street_address' => 'address',
                'town_city' => 'townCity',
                'region'   => 'region',
                'postal_code' => 'postalCode',
                'country' => 'country'
            ]
        );

        return $hydrator->hydrate(new User(), $response);
    }
}
