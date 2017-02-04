<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Steam;

use SocialConnect\Auth\Exception\InvalidResponse;
use SocialConnect\Auth\Provider\OpenID\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OpenID\AbstractProvider
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
     * {@inheritdoc}
     * @throws \SocialConnect\Auth\Exception\InvalidResponse
     */
    public function getIdentity(AccessToken $accessToken)
    {
        preg_match(
            "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/",
            $accessToken->getIdentity(),
            $matches
        );

        $userId = $matches[0];

        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'ISteamUser/GetPlayerSummaries/v0002/',
            [
                'key' => $this->consumer->getKey(),
                'steamids' => $userId
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
                $response->getBody()
            );
        }

        $hydrator = new ObjectMap(array(
            'steamid' => 'id',
            'personaname' => 'username',
            'realname' => 'fullname'
        ));

        return $hydrator->hydrate(new User(), $result->response->players[0]);
    }
}
