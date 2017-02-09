<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Vimeo extends \SocialConnect\OAuth2\AbstractProvider
{
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
        return 'vimeo';
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        $response = json_decode($body, true);
        if ($response) {
            $token = new AccessToken($response);

            // Vimeo return User on get Access Token Request (looks like to protect round trips)
            if (isset($response['user'])) {
                $hydrator = new ObjectMap(
                    [
                        'name' => 'fullname',
                    ]
                );

                $this->user = $hydrator->hydrate(new User(), (object) $response['user']);
                $this->user->id = str_replace('/users/', '', $this->user->uri);

                $token->setUid($this->user->id);
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
