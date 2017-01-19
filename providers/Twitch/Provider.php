<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Twitch;

use SocialConnect\Auth\Exception\InvalidAccessToken;
use SocialConnect\Auth\Exception\InvalidResponse;
use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OAuth2\AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.twitch.tv/kraken/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://api.twitch.tv/kraken/oauth2/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://api.twitch.tv/kraken/oauth2/token';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'twitch';
    }

    /**
     * @return array
     */
    public function getAuthUrlParameters()
    {
        return array(
            'client_id' => $this->consumer->getKey(),
            'redirect_uri' => $this->getRedirectUrl(),
            'response_type' => 'code'
        );
    }

    /**
     * @return string
     */
    public function getScopeInline()
    {
        // @link https://github.com/justintv/Twitch-API/blob/master/authentication.md#scopes
        return implode('+', $this->scope);
    }

    /**
     * @param $body
     * @return AccessToken
     * @throws InvalidAccessToken
     */
    public function parseToken($body)
    {
        $response = json_decode($body, false);
        if ($response) {
            if ($response->ok && $response->access_token) {
                return new AccessToken($response->access_token);
            }
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessToken $accessToken)
    {
        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'user',
            [
                'oauth_token' => $accessToken->getToken()
            ]
        );

        $result = $response->json();
        if (!$result) {
            throw new InvalidResponse(
                'API response is not a valid JSON object',
                $response->getBody()
            );
        }

        $hydrator = new ObjectMap(array(
            '_id' => 'id',
            'name' => 'name',
        ));

        return $hydrator->hydrate(new User(), $result->user);
    }
}
