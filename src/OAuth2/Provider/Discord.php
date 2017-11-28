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

class Discord extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://discordapp.com/api/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://discordapp.com/api/oauth2/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://discordapp.com/api/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'discord';
    }

    /**
     * @return string
     */
    public function getScopeInline()
    {
        return implode(' ', $this->scope);
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $result = json_decode($body, true);
        if ($result) {
            return new AccessToken($result);
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'users/@me',
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
                'verified' => 'emailVerified'
            ]
        );

        return $hydrator->hydrate(new User(), $result);
    }
}
