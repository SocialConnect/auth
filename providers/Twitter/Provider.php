<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Twitter;

use SocialConnect\Auth\Exception\InvalidResponse;
use SocialConnect\Auth\Provider\OAuth1\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OAuth1\AbstractProvider
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
    public function getIdentity(AccessToken $accessToken)
    {
        $this->consumerToken = $accessToken;

        $parameters = $this->requestTokenParams;
        $parameters['user_id'] = $accessToken->getUserId();

        $response = $this->oauthRequest(
            $this->getBaseUri() . 'users/lookup.json',
            Client::GET,
            $parameters,
            $this->requestTokenHeaders
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
            'id' => 'id',
            'name' => 'fullname',
            'screen_name' => 'username'
        ));

        return $hydrator->hydrate(new User(), $result[0]);
    }
}
