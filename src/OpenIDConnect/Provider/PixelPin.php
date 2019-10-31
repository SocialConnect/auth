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
use SocialConnect\JWX\DecodeOptions;
use SocialConnect\JWX\JWT;
use SocialConnect\OpenIDConnect\AccessToken;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\OpenIDConnect\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Provider\Exception\InvalidAccessToken;

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
     * {@inheritDoc}
     */
    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken->getToken()}";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken(string $body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $result = json_decode($body, true);
        if ($result) {
            $token = new AccessToken([
                'access_token' => $result['access_token'],
                'id_token' => $result['access_token'],
                'token_type' => $result['token_type']
            ]);
            $token->setJwt(
                JWT::decode($result['access_token'], $this->getJWKSet(), new DecodeOptions())
            );

            return $token;
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
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
        $response = $this->request('GET', 'connect/userinfo', [], $accessToken);

        $hydrator = new ArrayHydrator([
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
        ]);

        return $hydrator->hydrate(new User(), $response);
    }
}
