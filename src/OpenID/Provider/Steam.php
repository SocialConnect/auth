<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OpenID\Provider;

use SocialConnect\Common\Http\Request;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;
use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\stream_for;

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
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $query = [
            'key' => $this->consumer->getKey(),
            'steamids' => $accessToken->getUserId()
        ];

        $response = $this->executeRequest(
            $this->requestFactory->createRequest(
                'GET',
                $this->getBaseUri() . 'ISteamUser/GetPlayerSummaries/v0002/?' . build_query($query)
            )
        );

        $result = $this->hydrateResponse($response);

        $hydrator = new ObjectMap(
            [
                'steamid' => 'id',
                'personaname' => 'username',
                'realname' => 'fullname'
            ]
        );

        return $hydrator->hydrate(new User(), $result->response->players[0]);
    }
}
