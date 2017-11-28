<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Common\Hydrator\ObjectMap;

class Facebook extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * By default AbstractProvider use POST method, FB does not accept POST and return HTML page ᕙ(⇀‸↼‶)ᕗ
     *
     * @var string
     */
    protected $requestHttpMethod = Client::GET;

    public function getBaseUri()
    {
        return 'https://graph.facebook.com/v2.8/';
    }

    public function getAuthorizeUri()
    {
        return 'https://www.facebook.com/dialog/oauth';
    }

    public function getRequestTokenUri()
    {
        return 'https://graph.facebook.com/oauth/access_token';
    }

    public function getName()
    {
        return 'facebook';
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $result = json_decode($body, true);
        if ($result) {
            return new AccessToken($result);
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'me',
            [
                'access_token' => $accessToken->getToken(),
                'fields' => $this->getFieldsInline()
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
                'id' => 'id',
                'first_name' => 'firstname',
                'last_name' => 'lastname',
                'email' => 'email',
                'gender' => 'sex',
                'link' => 'url',
                'locale' => 'locale',
                'name' => 'fullname',
                'timezone' => 'timezone',
                'updated_time' => 'dateModified',
                'verified' => 'verified'
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $result);
        $user->emailVerified = true;

        return $user;
    }
}
