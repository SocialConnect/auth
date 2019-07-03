<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

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
    const NAME = 'vk';

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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken(string $body)
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
     * {@inheritDoc}
     */
    public function prepareRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        $query = [
            'v' => '5.100',
        ];

        if ($accessToken) {
            $query['access_token'] = $accessToken->getToken();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $query = [];

        $fields = $this->getArrayOption('identity.fields', []);
        if ($fields) {
            $query['fields'] = implode(',', $fields);
        }

        $response = $this->request('method/users.get', $query, $accessToken);

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
        $user = $hydrator->hydrate(new User(), $response->response[0]);

        if ($user->sex) {
            $user->sex = $user->sex === 1 ? 'female' : 'male';
        }

        $user->email = $this->email;
        $user->emailVerified = true;

        return $user;
    }
}
