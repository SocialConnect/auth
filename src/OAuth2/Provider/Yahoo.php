<?php
/**
 * SocialConnect project
 *
 * @author: Bogdan Popa https://github.com/icex <bogdan@pixelwattstudio.com>
 */

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;
use SocialConnect\OAuth2\AccessToken;

class Yahoo extends \SocialConnect\OAuth2\AbstractProvider
{
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
        return 'yahoo';
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        $result = json_decode($body, true);
        if ($result) {
            return new AccessToken($result);
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
    }

    /**
     * Retreive userId
     */
    private function getCurrentUserId(AccessTokenInterface $accessToken)
    {
        $parameters = [];
        $parameters['format'] = 'json';
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'me/guid',
            $parameters,
            Client::GET,
            [
                'Authorization' => 'Bearer ' . $accessToken->getToken(),
            ]
        );
        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API (get guid) response with error code',
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

        return $result->guid->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $uid = $this->getCurrentUserId($accessToken);

        $response = $this->httpClient->request(
            $this->getBaseUri() . 'user/' . $uid . '/profile',
            [
                'format' => 'json',
            ],
            Client::GET,
            [
                'Authorization' => 'Bearer ' . $accessToken->getToken(),
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

        $result = $result->profile;

        if (isset($result->image)) {
            $result->image = $result->image->imageUrl;
        }
        if ($result->emails) {
            // first one should do it, should be the default one
            $result->email = reset($result->emails);
            $result->email = $result->email->handle;
        }

        if ($result->ims) {
            $result->username = reset($result->ims);
            $result->username = $result->username->handle;
        }

        if ($result->birthdate) {
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
