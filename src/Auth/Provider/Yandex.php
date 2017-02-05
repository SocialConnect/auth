<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

use SocialConnect\Auth\AccessTokenInterface;
use SocialConnect\Auth\Provider\Exception\InvalidAccessToken;
use SocialConnect\Auth\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Yandex extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://login.yandex.ru/info';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://oauth.yandex.ru/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://oauth.yandex.ru/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'yandex';
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
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri(),
            [
                'oauth_token' => $accessToken->getToken(),
                'format' => 'json'
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
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'default_email' => 'email',
            'real_name' => 'fullname',
            'birthday' => 'birthday',
            'login' => 'username',
        ));

        return $hydrator->hydrate(new User(), $result);
    }
}
