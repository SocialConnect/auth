<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OpenID\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Common\Entity\User;

class Steam extends \SocialConnect\OpenID\AbstractProvider
{
    const NAME = 'steam';

    /**
     * {@inheritdoc}
     */
    public function getOpenIdUrl()
    {
        return 'https://steamcommunity.com/openid/id';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.steampowered.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param string $identity
     * @return string
     */
    protected function parseUserIdFromIdentity($identity)
    {
        preg_match(
            '/7[0-9]{15,25}/',
            $identity,
            $matches
        );

        return $matches[0];
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        $query['key'] = $this->consumer->getKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $query = [
            'steamids' => $accessToken->getUserId()
        ];

        $response = $this->request('GET', 'ISteamUser/GetPlayerSummaries/v0002/', $query, $accessToken);

        $hydrator = new ArrayHydrator([
            'steamid' => 'id',
            'personaname' => 'username',
            'realname' => 'fullname'
        ]);

        return $hydrator->hydrate(new User(), $response['response']['players'][0]);
    }
}
