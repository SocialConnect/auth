<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Slack;

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
        return 'https://slack.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://slack.com/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://slack.com/api/oauth.access';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'slack';
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

            throw new InvalidAccessToken('Slack response with ok == false');
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessToken $accessToken)
    {
        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'api/users.identity',
            [
                'token' => $accessToken->getToken()
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

        if (!$result->ok) {
            throw new InvalidResponse(
                'API response->ok is false',
                $result
            );
        }

        $hydrator = new ObjectMap(array(
            'id' => 'id',
            'name' => 'name',
        ));

        $user = $hydrator->hydrate(new User(), $result->user);
        $user->team = $result->team;

        return $user;
    }
}
