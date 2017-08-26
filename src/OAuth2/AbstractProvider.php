<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2;

use InvalidArgumentException;
use SocialConnect\OAuth2\Exception\InvalidState;
use SocialConnect\OAuth2\Exception\Unauthorized;
use SocialConnect\OAuth2\Exception\UnknownAuthorization;
use SocialConnect\OAuth2\Exception\UnknownState;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
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
        return [
            'client_id' => $this->consumer->getKey(),
            'redirect_uri' => $this->getRedirectUrl(),
            'response_type' => 'code',
        ];
    }

    /**
     * 16 bytes / 128 bit / 16 symbols / 32 symbols in hex
     */
    const STATE_BYTES = 16;

    /**
     * @return string
     */
    protected function generateState()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(self::STATE_BYTES));
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(self::STATE_BYTES, $strongSource);
            if (!$strongSource) {
                trigger_error(
                    'openssl was unable to use a strong source of entropy. ' .
                    'Consider updating your system libraries, or ensuring ' .
                    'you have more available entropy.',
                    E_USER_WARNING
                );
            }

            return bin2hex($bytes);
        }

        trigger_error(
            'You do not have a safe source of random data available. ' .
            'Install either the openssl extension, or paragonie/random_compat. ' .
            'Falling back to an insecure random source.',
            E_USER_WARNING
        );

        return md5(
            time() . mt_rand()
        );
    }

    /**
     * @return string
     */
    public function makeAuthUrl()
    {
        $urlParameters = $this->getAuthUrlParameters();

        $this->session->set(
            'oauth2_state',
            $urlParameters['state'] = $this->generateState()
        );

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
        $parameters = [
            'client_id' => $this->consumer->getKey(),
            'client_secret' => $this->consumer->getSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUrl()
        ];

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

        $response = $this->httpClient->fromRequest(
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
     * @throws \SocialConnect\OAuth2\Exception\InvalidState
     * @throws \SocialConnect\OAuth2\Exception\UnknownState
     * @throws \SocialConnect\OAuth2\Exception\UnknownAuthorization
     */
    public function getAccessTokenByRequestParameters(array $parameters)
    {
        $state = $this->session->get('oauth2_state');
        if (!$state) {
            throw new UnknownAuthorization();
        }

        if (isset($parameters['error']) && $parameters['error'] === 'access_denied') {
            throw new Unauthorized();
        }

        if (!isset($parameters['state'])) {
            throw new UnknownState();
        }

        if ($state !== $parameters['state']) {
            throw new InvalidState();
        }

        if (!isset($parameters['code'])) {
            throw new Unauthorized('Unknown code');
        }

        return $this->getAccessToken($parameters['code']);
    }
}
