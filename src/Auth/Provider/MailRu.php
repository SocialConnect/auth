<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

use SocialConnect\Auth\Provider\Exception\InvalidAccessToken;
use SocialConnect\Auth\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class MailRu extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'http://www.appsmail.ru/platform/api';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://connect.mail.ru/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://connect.mail.ru/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mail-ru';
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $result = json_decode($body);
        if ($result) {
            if (isset($result->access_token)) {
                $token = new AccessToken($result->access_token);
                $token->setUid($result->x_mailru_vid);

                return $token;
            }

            throw new InvalidAccessToken('Provider API returned without access_token field inside JSON');
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }

    /**
     * Copy/pasted from MailRU examples :)
     *
     * @param array $requestParameters
     * @return string
     */
    protected function makeSecureSignature(array $requestParameters)
    {
        ksort($requestParameters);

        $params = '';

        foreach ($requestParameters as $key => $value) {
            $params .= "$key=$value";
        }

        return md5($params . $this->consumer->getSecret());
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessToken $accessToken)
    {
        $parameters = [
            'client_id' => $this->consumer->getKey(),
            'format' => 'json',
            'method' => 'users.getInfo',
            'secure' => 1,
            'session_key' => $accessToken->getToken()
        ];

        $parameters['sig'] = $this->makeSecureSignature($parameters);

        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri(),
            $parameters
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
            'uid' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'nick' => 'username'
        ));

        $user = $hydrator->hydrate(new User(), $result[0]);

        if ($user->sex) {
            $user->sex = $user->sex === 1 ? 'female' : 'male';
        }

        return $user;
    }
}
