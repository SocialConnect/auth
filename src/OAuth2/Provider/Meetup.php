<?php
/**
 * SocialConnect project
 *
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;
use SocialConnect\OAuth2\AccessToken;

class Meetup extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'meetup';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.meetup.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://secure.meetup.com/oauth2/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://secure.meetup.com/oauth2/access';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        $result = json_decode($body, true);
        if ($result) {
            return new AccessToken($result);
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . '2/member/self?sign=true&photo-host=public&fields=gender',
            [
                'format' => 'json'
            ],
            Client::GET,
            [
                'Authorization' => 'Bearer ' . $accessToken->getToken(),
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

        $user = new User();

        $user->id         = $result->id;
        $user->username   = $result->name;
        $user->fullname   = $result->name;
        $user->sex        = $result->gender;
        $user->pictureURL = $result->photo->photo_link;

        return $user;
    }
}
