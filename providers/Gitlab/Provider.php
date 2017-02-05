<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Gitlab;

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
        return 'https://gitlab.com/api/v3/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://gitlab.com/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://gitlab.com/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'gitlab';
    }

    /**
     * @return string
     */
    public function getScopeInline()
    {
        return implode('+', $this->scope);
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $result = json_decode($body);
        if ($result) {
            if (isset($result->access_token)) {
                return new AccessToken($result->access_token);
            }

            throw new InvalidAccessToken('Provider API returned without access_token field inside JSON');
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessToken $accessToken)
    {
        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'user',
            [
                'access_token' => $accessToken->getToken()
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
                $response->getBody()
            );
        }

        $hydrator = new ObjectMap(array(
            'user_id' => 'id',
            'name' => 'fullname',
        ));

        return $hydrator->hydrate(new User(), $result);
    }
}
