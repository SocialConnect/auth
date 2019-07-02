<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class GitHub extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'github';

    public function getBaseUri()
    {
        return 'https://api.github.com/';
    }

    public function getAuthorizeUri()
    {
        return 'https://github.com/login/oauth/authorize';
    }

    public function getRequestTokenUri()
    {
        return 'https://github.com/login/oauth/access_token';
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
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'graphql',
            [
                'body' => json_encode([
                    'query' => 'query { viewer { databaseId, login, name, email, avatarUrl } }'
                ])
            ],
            [
                'Authorization' => "bearer {$accessToken->getToken()}",
                'Accept' => 'application/vnd.github.v4.idl',
                'Content-Type' => 'application/json'
            ],
            Client::POST
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
                'databaseId' => 'id',
                'login' => 'username',
                'email' => 'email',
                'avatarUrl' => 'pictureURL',
                'name' => 'fullname'
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $result->data->viewer);

        return $user;
    }
}
