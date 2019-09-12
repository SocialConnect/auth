<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\Provider\Exception\InvalidProviderConfiguration;
use SocialConnect\Common\HttpStack;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Provider\Session\SessionInterface;

abstract class AbstractBaseProvider
{
    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * @var array
     */
    protected $scope = [];

    /**
     * @var HttpStack
     */
    protected $httpStack;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param HttpStack $httpStack
     * @param SessionInterface $session
     * @param array $parameters
     * @throws InvalidProviderConfiguration
     */
    public function __construct(HttpStack $httpStack, SessionInterface $session, array $parameters)
    {
        if (isset($parameters['scope'])) {
            $this->setScope($parameters['scope']);
        }

        if (isset($parameters['redirectUri'])) {
            $this->redirectUri = $parameters['redirectUri'];
        }

        if (isset($parameters['options'])) {
            $this->options = $parameters['options'];
        }

        $this->consumer = $this->createConsumer($parameters);
        $this->httpStack = $httpStack;
        $this->session = $session;
    }

    /**
     * @param int $bytes Default it's 16 bytes / 128 bit / 16 symbols / 32 symbols in hex
     * @return string
     * @throws \Exception
     */
    protected function generateState(int $bytes = 16): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * @param array $parameters
     * @return Consumer
     * @throws InvalidProviderConfiguration
     */
    protected function createConsumer(array $parameters): Consumer
    {
        return new Consumer(
            $this->getRequiredStringParameter('applicationId', $parameters),
            $this->getRequiredStringParameter('applicationSecret', $parameters)
        );
    }

    /**
     * @param string $key
     * @param array $parameters
     * @return string
     * @throws InvalidProviderConfiguration
     */
    protected function getRequiredStringParameter(string $key, array $parameters): string
    {
        if (!isset($parameters[$key])) {
            throw new InvalidProviderConfiguration(
                "Parameter '{$key}' doesn`t exists for '{$this->getName()}' provider configuration"
            );
        }

        if (!is_string($parameters[$key])) {
            throw new InvalidProviderConfiguration(
                "Parameter '{$key}' must be string inside '{$this->getName()}' provider configuration"
            );
        }

        return $parameters[$key];
    }

    /**
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public function getBoolOption($key, $default): bool
    {
        if (array_key_exists($key, $this->options)) {
            return (bool) $this->options[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param array $default
     * @return array
     */
    public function getArrayOption($key, array $default = []): array
    {
        if (array_key_exists($key, $this->options)) {
            return (array) $this->options[$key];
        }

        return $default;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return str_replace('${provider}', $this->getName(), $this->redirectUri);
    }

    /**
     * Default parameters for auth url, can be redeclared inside implementation of the Provider
     *
     * @return array
     */
    public function getAuthUrlParameters(): array
    {
        return $this->getArrayOption('auth.parameters', []);
    }

    /**
     * @return string
     */
    abstract public function getBaseUri();

    /**
     * Return Provider's name
     *
     * @return string
     */
    abstract public function getName();

    /**
     * @param array $requestParameters
     * @return \SocialConnect\Provider\AccessTokenInterface
     */
    abstract public function getAccessTokenByRequestParameters(array $requestParameters);

    /**
     * @return string
     */
    abstract public function makeAuthUrl(): string;

    /**
     * Get current user identity from social network by $accessToken
     *
     * @param AccessTokenInterface $accessToken
     * @return \SocialConnect\Common\Entity\User
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \SocialConnect\Provider\Exception\InvalidResponse
     */
    abstract public function getIdentity(AccessTokenInterface $accessToken);

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function executeRequest(RequestInterface $request): ResponseInterface
    {
        $response = $this->httpStack->sendRequest($request);

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
     * @return array
     * @throws InvalidResponse
     */
    protected function hydrateResponse(ResponseInterface $response): array
    {
        $result = json_decode($response->getBody()->getContents(), true);
        if (!$result) {
            throw new InvalidResponse(
                'API response is not a valid JSON object',
                $response
            );
        }

        return $result;
    }

    /**
     * This is a lifecycle method, should be redeclared inside Provider when it's needed to mutate $query or $headers
     *
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @param array $query
     * @param AccessTokenInterface|null $accessToken Null is needed to allow send request for not OAuth
     */
    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $query['access_token'] = $accessToken->getToken();
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $query
     * @param AccessTokenInterface|null $accessToken
     * @param array|null $payload
     * @return array
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function request(string $method, string $url, array $query, AccessTokenInterface $accessToken = null, array $payload = null)
    {
        $headers = [];

        $this->prepareRequest(
            $method,
            $this->getBaseUri() . $url,
            $headers,
            $query,
            $accessToken
        );

        return $this->hydrateResponse(
            $this->executeRequest(
                $this->createRequest(
                    $method,
                    $this->getBaseUri() . $url,
                    $query,
                    $headers,
                    $payload
                )
            )
        );
    }

    /**
     * @return array
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param array $scope
     */
    public function setScope(array $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getScopeInline()
    {
        return implode(',', $this->scope);
    }

    /**
     * @return \SocialConnect\Provider\Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $query
     * @param array $headers
     * @param array|null $payload
     * @return RequestInterface
     */
    protected function createRequest(string $method, string $uri, array $query, array $headers, array $payload = null): RequestInterface
    {
        $url = $uri;

        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $request = $this->httpStack->createRequest($method, $url);

        foreach ($headers as $k => $v) {
            $request = $request->withHeader($k, $v);
        }

        $contentLength = 0;

        if ($payload) {
            $payloadAsString = http_build_query($payload);
            $contentLength = mb_strlen($payloadAsString);

            $request = $request
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

            return $request->withBody(
                $this->httpStack->createStream(
                    $payloadAsString
                )
            );
        }

        if ($request->getMethod() === 'POST') {
            $request = $request
                ->withHeader('Content-Length', $contentLength);
        }

        return $request;
    }
}
