<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Vimeo;

use SocialConnect\Auth\Exception\InvalidAccessToken;
use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OAuth2\AbstractProvider
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
        $response = json_decode($body, false);
        if ($response) {
            // Vimeo return User on get Access Token Request (looks like to protect round trips)
            if (isset($response->user)) {
                $hydrator = new ObjectMap(array(
                    'name' => 'fullname',
                ));

                $this->user = $hydrator->hydrate(new User(), $response->user);
                $this->user->id = str_replace('/users/', '', $this->user->uri);
            }

            if (isset($response->access_token)) {
                return new AccessToken($response->access_token);
            }

            throw new InvalidAccessToken('access_token field does not exists inside API JSON response');
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessToken $accessToken)
    {
        return $this->user;
    }
}
