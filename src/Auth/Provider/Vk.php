<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

use SocialConnect\Auth\AccessTokenInterface;
use SocialConnect\Auth\Provider\Exception\InvalidAccessToken;
use SocialConnect\Auth\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Common\Hydrator\ObjectMap;

class Vk extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected $requestHttpMethod = Client::GET;

    /**
     * Vk returns email inside AccessToken
     *
     * @var string|null
     */
    protected $email;

    public function getBaseUri()
    {
        return 'https://api.vk.com/';
    }

    public function getAuthorizeUri()
    {
        return 'https://oauth.vk.com/authorize';
    }

    public function getRequestTokenUri()
    {
        return 'https://oauth.vk.com/access_token';
    }

    public function getName()
    {
        return 'vk';
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        $result = json_decode($body);
        if (!$result) {
            throw new InvalidAccessToken;
        }

        if (!isset($result->access_token) || empty($result->access_token)) {
            throw new InvalidAccessToken;
        }

        if (isset($result->email)) {
            $this->email = $result->email;
        }

        $token = new AccessToken($result->access_token);
        $token->setUid($result->user_id);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'method/users.get',
            [
                'v' => '5.24',
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
                $response->getBody()
            );
        }

        $hydrator = new ObjectMap(array(
            'id' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'email' => 'email',
            'bdate' => 'birthday',
            'nickname' => 'username',
            'sex' => 'sex',
        ));

        $user = $hydrator->hydrate(new User(), $result->response[0]);

        if ($user->sex) {
            $user->sex = $user->sex === 1 ? 'female' : 'male';
        }

        $user->email = $this->email;

        return $user;
    }
}
