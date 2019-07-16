<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OpenIDConnect;

use SocialConnect\JWX\JWT;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;

abstract class AbstractProvider extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * @return array
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function discover(): array
    {
        $response = $this->executeRequest(
            $this->httpStack->createRequest('GET', $this->getOpenIdUrl())
        );

        $result = json_decode($response->getBody()->getContents(), true);
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
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getJWKSet(): array
    {
        $spec = $this->discover();

        if (!isset($spec['jwks_uri'])) {
            throw new \RuntimeException('Unknown jwks_uri inside OpenIDConnect specification');
        }

        $response = $this->executeRequest(
            $this->httpStack->createRequest('GET', $spec['jwks_uri'])
        );

        $result = json_decode($response->getBody()->getContents(), true);
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
        // Optional field...
        //$parameters['response_mode'] = 'form_post';
        $parameters['scope'] = 'openid';

        return $parameters;
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
            $token->setJwt(
                JWT::decode($result['id_token'], $this->getJWKSet())
            );

            return $token;
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }
}
