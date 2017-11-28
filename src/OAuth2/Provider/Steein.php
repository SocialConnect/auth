<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

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
        return 'steein';
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
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'api/v2.0/users/show',
            [],
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
                'displayName' => 'fullname',
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $result);

        if ($result->name) {
            if ($result->name->first_name) {
                $user->firstname = $result->name->first_name;
            }

            if ($result->name->last_name) {
                $user->lastname = $result->name->last_name;
            }
        }

        return $user;
    }
}
