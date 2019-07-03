<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */
declare(strict_types=1);

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
     * {@inheritDoc}
     */
    public function prepareRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        $query['format'] = 'json';

        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken->getToken()}";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('2/member/self?sign=true&photo-host=public&fields=gender', [], $accessToken);

        $user = new User();

        $user->id         = $response->id;
        $user->username   = $response->name;
        $user->fullname   = $response->name;
        $user->sex        = $response->gender;
        $user->pictureURL = $response->photo->photo_link;

        return $user;
    }
}
