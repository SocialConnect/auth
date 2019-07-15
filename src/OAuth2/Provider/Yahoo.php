<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author: Bogdan Popa https://github.com/icex <bogdan@pixelwattstudio.com>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;
use SocialConnect\OAuth2\AccessToken;

class Yahoo extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'yahoo';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://social.yahooapis.com/v1/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://api.login.yahoo.com/oauth2/request_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://api.login.yahoo.com/oauth2/get_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken(string $body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $result = json_decode($body, true);
        if ($result) {
            $token = new AccessToken($result);
            $token->setUid($result['xoauth_yahoo_guid']);

            return $token;
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        $query['format'] = 'json';

        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken->getToken()}";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('GET', "user/{$accessToken->getUserId()}/profile", [], $accessToken);

        $result = $response->profile;

        if (isset($result->image)) {
            $result->image = $result->image->imageUrl;
        }

        if (isset($result->emails)) {
            // first one should do it, should be the default one
            $result->email = reset($result->emails);
            $result->email = $result->email->handle;
        }

        if (isset($result->ims)) {
            $result->username = reset($result->ims);
            $result->username = $result->username->handle;
        }

        if (isset($result->birthdate)) {
            $result->birth_date = date('Y-m-d', strtotime($result->birthdate . '/' . $result->birthYear));
        }

        $hydrator = new ObjectMap(
            [
                'guid'       => 'id',
                'image'      => 'picture',
                'email'      => 'email',
                'givenName'  => 'firstname',
                'familyName' => 'lastname',
                'username'   => 'username',
                'gender'     => 'gender',
                'birth_date' => 'birth_date',
            ]
        );

        return $hydrator->hydrate(new User(), $result);
    }
}
