<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Bitbucket extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'bitbucket';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.bitbucket.org/2.0/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://bitbucket.org/site/oauth2/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://bitbucket.org/site/oauth2/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    public function parseToken(string $body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $result = json_decode($body, true);
        if ($result) {
            return new AccessToken($result);
        }

        throw new InvalidAccessToken('Server response with not valid/empty JSON');
    }


    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('user', [], $accessToken);

        $hydrator = new ObjectMap(
            [
                'uuid' => 'id',
                'display_name' => 'fullname',
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $response);
        $user->pictureURL = "https://bitbucket.org/account/{$user->username}/avatar/512/";

        return $user;
    }
}
