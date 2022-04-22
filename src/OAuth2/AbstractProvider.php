<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2;

use Psr\Http\Message\RequestInterface;
use SocialConnect\OAuth2\Exception\InvalidState;
use SocialConnect\OAuth2\Exception\Unauthorized;
use SocialConnect\OAuth2\Exception\UnknownAuthorization;
use SocialConnect\OAuth2\Exception\UnknownState;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;

abstract class AbstractProvider extends AbstractBaseProvider
{
    /**
     * HTTP method for access token request
     *
     * @var string
     */
    protected $requestHttpMethod = 'POST';

    /**
     * @return string
     */
    abstract public function getAuthorizeUri();

    /**
     * @return string
     */
    abstract public function getRequestTokenUri();

    /**
     * {@inheritdoc}
     */
    public function getAuthUrlParameters(): array
    {
        $parameters = parent::getAuthUrlParameters();

        // special parameters only required for OAuth2
        $parameters['client_id'] = $this->consumer->getKey();
        $parameters['redirect_uri'] = $this->getRedirectUrl();
        $parameters['response_type'] = 'code';

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function makeAuthUrl(): string
    {
        $urlParameters = $this->getAuthUrlParameters();

        if (!$this->getBoolOption('stateless', false)) {
            $this->session->set(
                'oauth2_state',
                $urlParameters['state'] = $this->generateState()
            );
        }

        if (count($this->scope) > 0) {
            $urlParameters['scope'] = $this->getScopeInline();
        }

        return $this->getAuthorizeUri() . '?' . http_build_query($urlParameters);
    }

    /**
     * Parse access token from response's $body
     *
     * @param string $body
     * @return AccessToken
     * @throws InvalidAccessToken
     */
    public function parseToken(string $body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $token = json_decode($body, true);
        if ($token) {
            if (!is_array($token)) {
                throw new InvalidAccessToken('Response must be array');
            }

            return new AccessToken($token);
        }

        throw new InvalidAccessToken('Server response with not valid/empty JSON');
    }

    /**
     * @param string $code
     * @return RequestInterface
     */
    protected function makeAccessTokenRequest(string $code): RequestInterface
    {
        $parameters = [
            'client_id' => $this->consumer->getKey(),
            'client_secret' => $this->consumer->getSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUrl()
        ];

        return $this->httpStack->createRequest($this->requestHttpMethod, $this->getRequestTokenUri())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->httpStack->createStream(http_build_query($parameters, '', '&')))
        ;
    }

    /**
     * @param string $refreshToken
     * @return RequestInterface
     */
    protected function makeRefreshAccessTokenRequest(string $refreshToken): RequestInterface
    {
        $parameters = [
            'refresh_token' => $refreshToken,
            'client_id' => $this->consumer->getKey(),
            'client_secret' => $this->consumer->getSecret(),
            'grant_type' => 'refresh_token',
        ];

        return $this->httpStack->createRequest($this->requestHttpMethod, $this->getRequestTokenUri())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->httpStack->createStream(http_build_query($parameters, '', '&')))
        ;
    }

    /**
     * @param string $code
     * @return AccessToken
     * @throws InvalidAccessToken
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAccessToken(string $code): AccessToken
    {
        $response = $this->executeRequest(
            $this->makeAccessTokenRequest($code)
        );

        return $this->parseToken($response->getBody()->getContents());
    }


    /**
     * @param string $refreshToken
     *
     * @return AccessToken
     * @throws InvalidAccessToken
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function refreshAccessToken(string $refreshToken): AccessToken
    {
        $response = $this->executeRequest(
            $this->makeRefreshAccessTokenRequest($refreshToken)
        );

        return $this->parseToken($response->getBody()->getContents());
    }

    /**
     * @param array $parameters
     * @return AccessToken
     * @throws InvalidAccessToken
     * @throws InvalidResponse
     * @throws InvalidState
     * @throws Unauthorized
     * @throws UnknownAuthorization
     * @throws UnknownState
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAccessTokenByRequestParameters(array $parameters)
    {
        if (isset($parameters['error']) && $parameters['error'] === 'access_denied') {
            throw new Unauthorized();
        }

        if (!isset($parameters['code'])) {
            throw new Unauthorized('Unknown code');
        }

        if (!$this->getBoolOption('stateless', false)) {
            $state = $this->session->get('oauth2_state');
            if (!$state) {
                throw new UnknownAuthorization();
            }

            if (!isset($parameters['state'])) {
                throw new UnknownState();
            }

            if ($state !== $parameters['state']) {
                throw new InvalidState();
            }
        }

        return $this->getAccessToken($parameters['code']);
    }

    /**
     * {@inheritDoc}
     */
    public function createAccessToken(array $information)
    {
        return new AccessToken($information);
    }
}
