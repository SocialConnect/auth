<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */

namespace SocialConnect\OpenIDConnect\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OpenIDConnect\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;
use SocialConnect\Common\Http\Client\Client;

/**
 * Class Provider
 * @package SocialConnect\Google
 */
class PixelPin extends AbstractProvider
{
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
        return 'pixelpin';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'connect/userinfo',
            [
            'access_token' => $accessToken->getToken()
            ],
            Client::GET,
            [
                'Authorization' => 'Bearer ' . $accessToken->getToken()
            ]
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $result = $response->json();
        if (!$result) {
            throw new InvalidResponse(
                'API response is not a valid JSON object',
                $response
            );
        }

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

        return $hydrator->hydrate(new User(), $result);
    }
}
