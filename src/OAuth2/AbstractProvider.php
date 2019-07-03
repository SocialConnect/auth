<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\OAuth2\Exception\InvalidState;
use SocialConnect\OAuth2\Exception\Unauthorized;
use SocialConnect\OAuth2\Exception\UnknownAuthorization;
use SocialConnect\OAuth2\Exception\UnknownState;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Http\Client\Client;
use function GuzzleHttp\Psr7\build_query;

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
     * 16 bytes / 128 bit / 16 symbols / 32 symbols in hex
     */
    const STATE_BYTES = 16;

    /**
     * @return string
     * @throws \Exception
     */
    protected function generateState(): string
    {
        return bin2hex(random_bytes(self::STATE_BYTES));
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

        parse_str($body, $token);

        if (!is_array($token) || !isset($token['access_token'])) {
            throw new InvalidAccessToken('Provider API returned an unexpected response');
        }

        return new AccessToken($token);
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

        return new \GuzzleHttp\Psr7\Request(
            $this->requestHttpMethod,
            $this->getRequestTokenUri(),
            [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            http_build_query($parameters)
        );
    }

    /**
     * @param string $code
     * @return AccessToken
     * @throws InvalidAccessToken
     * @throws InvalidResponse
     */
    public function getAccessToken(string $code): AccessToken
    {
        $response = $this->executeRequest(
            $this->makeAccessTokenRequest($code)
        );

        return $this->parseToken($response->getBody()->getContents());
    }

    /**
     * This is a lifecycle method, should be redeclared inside Provider when it's needed to mutate $query or $headers
     *
     * @param array $headers
     * @param array $query
     * @param AccessTokenInterface|null $accessToken Null is needed to allow send request for not OAuth
     */
    public function prepareRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $query['access_token'] = $accessToken->getToken();
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function executeRequest(RequestInterface $request): ResponseInterface
    {
        $response = $this->httpClient->sendRequest($request);

        $statusCode = $response->getStatusCode();
        if (200 <= $statusCode && 300 > $statusCode) {
            return $response;
        }

        throw new InvalidResponse(
            'API response with error code',
            $response
        );
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     * @throws InvalidResponse
     */
    protected function hydrateResponse(ResponseInterface $response)
    {
        $result = json_decode($response->getBody()->getContents(), false);
        if (!$result) {
            throw new InvalidResponse(
                'API response is not a valid JSON object',
                $response
            );
        }

        return $result;
    }

    /**
     * @param string $uri
     * @param array $query
     * @param AccessTokenInterface $accessToken
     * @return mixed
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function request(string $uri, array $query, AccessTokenInterface $accessToken)
    {
        $headers = [];

        $this->prepareRequest(
            $headers,
            $query,
            $accessToken
        );

        $url = $this->getBaseUri() . $uri;

        if ($query) {
            $url .= '?' . build_query($query);
        }

        $response = $this->executeRequest(
            new \GuzzleHttp\Psr7\Request(
                'GET',
                $url,
                $headers,
                null
            )
        );

        return $this->hydrateResponse($response);
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
}
