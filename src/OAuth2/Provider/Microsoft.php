<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Microsoft extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'microsoft';

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
        return self::NAME;
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
        $response = $this->request('me', [], $accessToken);

        $hydrator = new ObjectMap(
            [
                'id' => 'id',
                'first_name' => 'firstname',
                'last_name' => 'lastname',
                'name' => 'fullname',
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $response);

        if ($response->emails) {
            if ($response->emails->preferred) {
                $user->email = $response->emails->preferred;
            } elseif ($response->emails->account) {
                $user->email = $response->emails->account;
            } elseif ($response->emails->personal) {
                $user->email = $response->emails->personal;
            }
        }

        return $user;
    }
}
