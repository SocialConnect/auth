<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OpenID\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Steam extends \SocialConnect\OpenID\AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getOpenIdUrl()
    {
        return 'http://steamcommunity.com/openid/id';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'http://api.steampowered.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'steam';
    }

    /**
     * @param string $identity
     * @return string
     */
    protected function parseUserIdFromIdentity($identity)
    {
        preg_match(
            '/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/',
            $identity,
            $matches
        );

        return $matches[1];
    }


    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'ISteamUser/GetPlayerSummaries/v0002/',
            [
                'key' => $this->consumer->getKey(),
                'steamids' => $accessToken->getUserId()
            ]
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $result = $response->json();
        if (!$result) {
            throw new InvalidResponse(
                'API response is not a valid JSON object',
                $response
            );
        }

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
