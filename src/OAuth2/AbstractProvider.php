<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2;

use InvalidArgumentException;
use SocialConnect\Auth\AbstractBaseProvider;
use SocialConnect\Auth\Provider\Exception\InvalidAccessToken;
use SocialConnect\Auth\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Http\Client\Client;

abstract class AbstractProvider extends AbstractBaseProvider
{
    /**
     * HTTP method for access token request
     *
     * @var string
     */
    protected $requestHttpMethod = Client::POST;

    /**
     * @return string
     */
    abstract public function getAuthorizeUri();

    /**
     * @return string
     */
    abstract public function getRequestTokenUri();

    /**
     * Default parameters for auth url, can be redeclared inside implementation of the Provider
     *
     * @return array
     */
    public function getAuthUrlParameters()
    {
        return array(
            'client_id' => $this->consumer->getKey(),
            'redirect_uri' => $this->getRedirectUrl(),
            'response_type' => 'code',
        );
    }

    /**
     * @return string
     */
    public function makeAuthUrl()
    {
        $urlParameters = $this->getAuthUrlParameters();

        if (count($this->scope) > 0) {
            $urlParameters['scope'] = $this->getScopeInline();
        }

        if (count($this->fields) > 0) {
            $urlParameters['fields'] = $this->getFieldsInline();
        }

        return $this->getAuthorizeUri() . '?' . http_build_query($urlParameters);
    }

    /**
     * Parse access token from response's $body
     *
     * @param string|bool $body
     * @return AccessToken
     * @throws InvalidAccessToken
     */
    public function parseToken($body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        parse_str($body, $token);

        if (!is_array($token) || !isset($token['access_token'])) {
            throw new InvalidAccessToken('Provider API returned an unexpected response');
        }

        return new AccessToken($token);
    }

    /**
     * @param string $code
     * @return \SocialConnect\Common\Http\Request
     */
    protected function makeAccessTokenRequest($code)
    {
        $parameters = array(
            'client_id' => $this->consumer->getKey(),
            'client_secret' => $this->consumer->getSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUrl()
        );

        return new \SocialConnect\Common\Http\Request(
            $this->getRequestTokenUri(),
            $parameters,
            $this->requestHttpMethod,
            [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        );
    }

    /**
     * @param string $code
     * @return AccessToken
     * @throws InvalidResponse
     */
    public function getAccessToken($code)
    {
        if (!is_string($code)) {
            throw new InvalidArgumentException('Parameter $code must be a string');
        }

        $response = $this->service->getHttpClient()->fromRequest(
            $this->makeAccessTokenRequest($code)
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $body = $response->getBody();
        return $this->parseToken($body);
    }


    /**
     * @param array $parameters
     * @return AccessToken
     */
    public function getAccessTokenByRequestParameters(array $parameters)
    {
        return $this->getAccessToken($parameters['code']);
    }
}
