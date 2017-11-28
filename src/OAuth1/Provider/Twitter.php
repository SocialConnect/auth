<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Common\Hydrator\ObjectMap;

class Twitter extends \SocialConnect\OAuth1\AbstractProvider
{
    public function getBaseUri()
    {
        return 'https://api.twitter.com/1.1/';
    }

    public function getAuthorizeUri()
    {
        return 'https://api.twitter.com/oauth/authenticate';
    }

    public function getRequestTokenUri()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    public function getRequestTokenAccessUri()
    {
        return 'https://api.twitter.com/oauth/access_token';
    }

    public function getName()
    {
        return 'twitter';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $this->consumerToken = $accessToken;

        $parameters = [
            'oauth_consumer_key' => $this->consumer->getKey(),
            'oauth_token' => $accessToken->getToken(),
            // String is expected because Twitter is awful
            'include_email' => 'true'
        ];

        // @link https://dev.twitter.com/rest/reference/get/account/verify_credentials
        $response = $this->oauthRequest(
            $this->getBaseUri() . 'account/verify_credentials.json',
            Client::GET,
            $parameters
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
                'name' => 'fullname',
                'screen_name' => 'username'
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $result);

        // When set to true email will be returned in the user objects as a string.
        // If the user does not have an email address on their account,
        // or if the email address is not verified, null will be returned.
        $user->emailVerified = true;

        return $user;
    }
}
