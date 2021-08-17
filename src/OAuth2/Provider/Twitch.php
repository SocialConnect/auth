<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Common\Entity\User;

class Twitch extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'twitch';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.twitch.tv/helix/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://id.twitch.tv/oauth2/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://id.twitch.tv/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getScopeInline()
    {
        // @link https://github.com/justintv/Twitch-API/blob/master/authentication.md#scopes
        return implode('+', $this->scope);
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken->getToken()}";
            $headers['Client-Id'] = $this->consumer->getKey();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('GET', 'users', [], $accessToken);

        $hydrator = new ArrayHydrator([
            'id' => 'id',
            'display_name' => 'fullname', // Custom Capitalized Users name
            'login' => 'username',
            'profile_image_url' => 'pictureURL',
            'email' => 'email'
        ]);

        return $hydrator->hydrate(new User(), $response['data'][0]);
    }
}
