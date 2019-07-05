<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2;

use Psr\Http\Message\RequestInterface;
use SocialConnect\Common\Http\Request;
use SocialConnect\OAuth2\Exception\InvalidState;
use SocialConnect\OAuth2\Exception\Unauthorized;
use SocialConnect\OAuth2\Exception\UnknownAuthorization;
use SocialConnect\OAuth2\Exception\UnknownState;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\AccessTokenInterface;
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

        $result = json_decode($body, true);
        if ($result) {
            return new AccessToken($result);
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
     * @param string $url
     * @param array $query
     * @param AccessTokenInterface $accessToken
     * @return mixed
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function request(string $url, array $query, AccessTokenInterface $accessToken)
    {
        $headers = [];

        $this->prepareRequest(
            $headers,
            $query,
            $accessToken
        );

        $uri = $this->getBaseUri() . $url;

        if (count($query)) {
            $uri .= '?' . http_build_query($query);
        }

        $request = $this->httpStack->createRequest('GET', $uri);

        foreach ($headers as $k => $v) {
            $request = $request->withHeader($k, $v);
        }

        return $this->hydrateResponse(
            $this->executeRequest($request)
        );
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
}
