<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Steein extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'steein';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://www.steein.ru/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://www.steein.ru/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://www.steein.ru/oauth/token';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getScopeInline()
    {
        return implode(' ', $this->scope);
    }

    /**
     * @param $body
     * @return AccessToken
     * @throws InvalidAccessToken
     */
    public function parseToken($body)
    {
        $response = json_decode($body, true);
        if ($response) {
            return new AccessToken($response);
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
    }

    /**
     * {@inheritDoc}
     */
    public function signRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken->getToken()}";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('api/v2.0/users/show', [], $accessToken);

        $hydrator = new ObjectMap(
            [
                'displayName' => 'fullname',
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $response);

        if ($response->name) {
            if ($response->name->first_name) {
                $user->firstname = $response->name->first_name;
            }

            if ($response->name->last_name) {
                $user->lastname = $response->name->last_name;
            }
        }

        return $user;
    }
}
