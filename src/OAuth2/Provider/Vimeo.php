<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;

class Vimeo extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'vimeo';

    /**
     * @var User|null
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.vimeo.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://api.vimeo.com/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://api.vimeo.com/oauth/access_token';
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
    public function parseToken(string $body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $response = json_decode($body, true);
        if ($response) {
            $token = new AccessToken($response);

            // Vimeo return User on get Access Token Request (looks like to protect round trips)
            if (isset($response['user'])) {
                $hydrator = new ArrayHydrator([
                    'name' => 'fullname',
                ]);

                /** @var \SocialConnect\Common\Entity\User $user */
                $user = $hydrator->hydrate(new User(), $response['user']);
                $this->user = $user;
                $this->user->id = str_replace('/users/', '', $response['user']['uri']);

                $token->setUserId((string) $this->user->id);
            }

            return $token;
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        return $this->user;
    }
}
