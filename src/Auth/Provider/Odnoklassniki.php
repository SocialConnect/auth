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
use SocialConnect\Common\Hydrator\ObjectMap;

/**
 * @link https://apiok.ru/dev
 */
class Odnoklassniki extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.ok.ru/api/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'http://connect.ok.ru/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'http://api.odnoklassniki.ru/oauth/token.do';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'odnoklassniki';
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
                return new AccessToken($result->access_token);
            }

            throw new InvalidAccessToken('Provider API returned without access_token field inside JSON');
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }

    /**
     * @link https://apiok.ru/dev/methods/
     *
     * @param array $requestParameters
     * @param AccessTokenInterface $accessToken
     * @return string
     */
    protected function makeSecureSignature(array $requestParameters, AccessTokenInterface $accessToken)
    {
        ksort($requestParameters);

        $params = '';

        foreach ($requestParameters as $key => $value) {
            if ($key === 'access_token') {
                continue;
            }

            $params .= "$key=$value";
        }

        return strtolower(md5($params . md5($accessToken->getToken() . $this->consumer->getSecret())));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $parameters = [
            'application_key' => $this->consumer->getPublic(),
            'access_token' => $accessToken->getToken(),
            'format' => 'json'
        ];

        $parameters['sig'] = $this->makeSecureSignature($parameters, $accessToken);

        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'users/getCurrentUser',
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
            'name' => 'fullname'
        ));

        return $hydrator->hydrate(new User(), $result);
    }
}
