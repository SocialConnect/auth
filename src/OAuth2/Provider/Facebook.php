<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use Psr\Http\Message\RequestInterface;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Common\Hydrator\ObjectMap;
use function GuzzleHttp\Psr7\build_query;

class Facebook extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'facebook';

    public function getBaseUri()
    {
        return 'https://graph.facebook.com/v3.3/';
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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken(string $body): AccessToken
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
        $query = [];

        $fields = $this->getArrayOption('identity.fields', []);
        if ($fields) {
            $query['fields'] = implode(',', $fields);
        }

        $response = $this->request(
            'me',
            $query,
            $accessToken
        );

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
        $user = $hydrator->hydrate(new User(), $response);
        $user->emailVerified = true;

        if (!empty($response->picture)) {
            $user->pictureURL = $response->picture->data->url;
        }

        return $user;
    }
}
