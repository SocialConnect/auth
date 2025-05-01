<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;

class Slack extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'slack';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://slack.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://slack.com/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://slack.com/api/oauth.access';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, ?AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $query['token'] = $accessToken->getToken();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('GET', 'api/users.identity', [], $accessToken);

        if (!$response['ok']) {
            throw new InvalidResponse(
                'API response->ok is false'
            );
        }

        $hydrator = new ArrayHydrator([
            'id' => 'id',
            'name' => 'name',
        ]);

        $user = $hydrator->hydrate(new User(), $response['user']);
        $user->team = $response['team'];

        return $user;
    }
}
