<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

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
     * {@inheritdoc}
     */
    public function getAuthUrlParameters(): array
    {
        $parameters = parent::getAuthUrlParameters();

        // special parameters only required for OpenIDConnect
        $parameters['client_id'] = $this->consumer->getKey();
        $parameters['redirect_uri'] = $this->getRedirectUrl();
        $parameters['response_type'] = 'code';
        $parameters['response_mode'] = 'form_post';
        $parameters['scope'] = 'openid';

        return $parameters;
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
