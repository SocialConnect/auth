<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
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
        $result = json_decode($body, true);
        if (!$result) {
            throw new InvalidAccessToken;
        }

        if (isset($result['email'])) {
            $this->email = $result['email'];
        }

        return new AccessToken($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'method/users.get',
            [
                'v' => '5.100',
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
                'bdate' => 'birthday',
                'screen_name' => 'username',
                'sex' => 'sex',
                'photo_max_orig' => 'pictureURL',
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $result->response[0]);

        if ($user->sex) {
            $user->sex = $user->sex === 1 ? 'female' : 'male';
        }

        $user->email = $this->email;
        $user->emailVerified = true;

        return $user;
    }
}
