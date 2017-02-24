<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OpenIDConnect;

use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;

abstract class AbstractProvider extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * @return array
     * @throws InvalidResponse
     */
    public function discover()
    {
        $response = $this->httpClient->request(
            $this->getOpenIdUrl()
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $result = $response->json(true);
        if (!$result) {
            throw new InvalidResponse(
                'API response without valid JSON',
                $response
            );
        }

        return $result;
    }

    /**
     * @return array
     * @throws InvalidResponse
     */
    public function getJWKSet()
    {
        $spec = $this->discover();

        if (!isset($spec['jwks_uri'])) {
            throw new \RuntimeException('Unknown jwks_uri inside OpenIDConnect specification');
        }

        $response = $this->httpClient->request(
            $spec['jwks_uri']
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $result = $response->json(true);
        if (!$result) {
            throw new InvalidResponse(
                'API response without valid JSON',
                $response
            );
        }

        if (!isset($result['keys'])) {
            throw new InvalidResponse(
                'API response without "keys" key inside JSON',
                $response
            );
        }

        return $result['keys'];
    }

    /**
     * @return string
     */
    abstract public function getOpenIdUrl();

    /**
     * Default parameters for auth url, can be redeclared inside implementation of the Provider
     *
     * @return array
     */
    public function getAuthUrlParameters()
    {
        return [
            'client_id' => $this->consumer->getKey(),
            'redirect_uri' => $this->getRedirectUrl(),
            'response_type' => 'code',
            //'response_mode' => 'form_post',
            'scope' => 'openid'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        $result = json_decode($body, true);
        if ($result) {
            $token = new AccessToken($result);
            $token->setJwt(
                JWT::decode($result['id_token'], $this->getJWKSet())
            );

            return $token;
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }
}
