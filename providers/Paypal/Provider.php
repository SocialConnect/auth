<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Paypal;

use SocialConnect\Auth\Exception\InvalidAccessToken;
use SocialConnect\Auth\Provider\OpenID\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class Provider extends \SocialConnect\Auth\Provider\OpenID\AbstractProvider
{
    protected $debug = true;

    protected $requestTokenUri = 'https://api.sandbox.paypal.com/v1/identity/openidconnect/tokenservice';

    /**
     * @return array
     */
    public function getAuthUrlParameters()
    {
        $default = parent::getAuthUrlParameters();
        $default['response_type'] = 'code';
        $default['nonce'] = time() . rand();
        $default['state'] = md5(uniqid(rand(), true));
        $default['redirect_uri'] = urlencode($this->getRedirectUrl());

        return $default;
    }

    public function getBaseUri()
    {
        return 'https://api.paypal.com/';
    }

    public function getAuthorizeUri()
    {
        return 'https://sandbox.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize';
    }

    public function getRequestTokenUri()
    {
        return $this->requestTokenUri;
    }

    public function getName()
    {
        return 'paypal';
    }

    /**
     * @param string $body
     * @return AccessToken
     * @throws InvalidAccessToken
     */
    public function parseToken($body)
    {
        $result = json_decode($body);

        if (!isset($result->access_token) || empty($result->access_token)) {
            throw new InvalidAccessToken;
        }

        return new AccessToken($result->access_token);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessToken $accessToken)
    {
        $response = $this->service->getHttpClient()->request(
            $this->getBaseUri() . 'method/users.get',
            [
                'v' => '5.24',
                'access_token' => $accessToken->getToken()
            ]
        );

        $body = $response->getBody();
        $result = json_decode($body);

        $hydrator = new ObjectMap(array(
            'id' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'email' => 'email'
        ));

        return $hydrator->hydrate(new User(), $result->response[0]);
    }
}
