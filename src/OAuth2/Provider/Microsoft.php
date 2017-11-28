<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Microsoft extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://apis.live.net/v5.0/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://login.live.com/oauth20_authorize.srf';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://login.live.com/oauth20_token.srf';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'microsoft';
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
            $this->getBaseUri() . 'me',
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
                $response
            );
        }

        $hydrator = new ObjectMap(
            [
                'id' => 'id',
                'first_name' => 'firstname',
                'last_name' => 'lastname',
                'name' => 'fullname',
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $result);

        if ($result->emails) {
            if ($result->emails->preferred) {
                $user->email = $result->emails->preferred;
            } elseif ($result->emails->account) {
                $user->email = $result->emails->account;
            } elseif ($result->emails->personal) {
                $user->email = $result->emails->personal;
            }
        }

        return $user;
    }
}
