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
        return 'https://api.twitch.tv/kraken/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://api.twitch.tv/kraken/oauth2/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://api.twitch.tv/kraken/oauth2/token';
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
    public function signRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $query['oauth_token'] = $accessToken->getToken();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('GET', 'user', [], $accessToken);

        $hydrator = new ArrayHydrator([
            '_id' => 'id',
            'display_name' => 'fullname', // Custom Capitalized Users name
            'name' => 'username',
        ]);

        return $hydrator->hydrate(new User(), $response);
    }
}
