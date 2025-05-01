<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth1;

use Psr\Http\Message\ResponseInterface;
use SocialConnect\Common\Exception\Unsupported;
use SocialConnect\Common\HttpStack;
use SocialConnect\OAuth1\Exception\Unauthorized;
use SocialConnect\OAuth1\Exception\UnknownAuthorization;
use SocialConnect\OAuth1\Signature\AbstractSignatureMethod;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth1\Exception\InvalidRequestToken;
use SocialConnect\OAuth1\Signature\MethodHMACSHA1;
use SocialConnect\Provider\Session\SessionInterface;

abstract class AbstractProvider extends AbstractBaseProvider
{
    /**
     * @var string
     */
    protected $oauth1Version = '1.0a';

    /**
     * @var string
     */
    protected $requestTokenMethod = 'POST';

    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * @var Token
     */
    protected $consumerToken;

    /**
     * @var array
     */
    protected $scope = [];

    /**
     * @var AbstractSignatureMethod
     */
    protected $signature;

    /**
     * {@inheritDoc}
     */
    public function __construct(HttpStack $httpStack, SessionInterface $session, array $parameters)
    {
        parent::__construct($httpStack, $session, $parameters);

        $this->consumerToken = new Token('', '');
        $this->signature = new MethodHMACSHA1();
    }

    /**
     * @return string
     */
    abstract public function getAuthorizeUri();

    /**
     * @return string
     */
    abstract public function getRequestTokenUri();

    /**
     * @return string
     */
    abstract public function getRequestTokenAccessUri();

    /**
     * @return Token
     * @throws InvalidRequestToken
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function requestAuthToken()
    {
        $parameters = [];

        /**
         * OAuth Core 1.0 Revision A: oauth_callback: An absolute URL to which the Service Provider will redirect
         * the User back when the Obtaining User Authorization step is completed.
         *
         * http://oauth.net/core/1.0a/#auth_step1
         */
        if ('1.0a' === $this->oauth1Version) {
            $parameters['oauth_callback'] = $this->getRedirectUrl();
        }

        $response = $this->oauthRequest(
            $this->getRequestTokenUri(),
            $this->requestTokenMethod,
            $parameters
        );

        $token = $this->parseToken($response->getBody()->getContents());
        $this->session->set('oauth1_request_token', $token);

        return $token;
    }

    /**
     * Parse Token from response's $body
     *
     * @param string|boolean $body
     * @return Token
     * @throws InvalidRequestToken
     * @throws InvalidResponse
     */
    public function parseToken($body)
    {
        if (empty($body)) {
            throw new InvalidResponse('Provider response with empty body');
        }

        parse_str($body, $token);
        if (!is_array($token) || !isset($token['oauth_token']) || !isset($token['oauth_token_secret'])) {
            throw new InvalidRequestToken;
        }

        return new Token($token['oauth_token'], $token['oauth_token_secret']);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $query
     * @return string
     */
    public function getSignatureBaseString(string $method, string $uri, array $query): string
    {
        $parts = [
            $method,
            $uri,
            Util::buildHttpQuery(
                $query
            )
        ];

        $parts = Util::urlencodeRFC3986($parts);

        return implode('&', $parts);
    }

    public function authorizationHeader(array $query)
    {
        ksort($query);

        $parameters = http_build_query(
            $query,
            '',
            ', ',
            PHP_QUERY_RFC3986
        );

        return "OAuth $parameters";
    }

    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, ?AccessTokenInterface $accessToken = null): void
    {
        $headers['Accept'] = 'application/json';

        $query['oauth_version'] = '1.0';
        $query['oauth_nonce'] = $this->generateState();
        $query['oauth_timestamp'] = time();
        $query['oauth_consumer_key'] = $this->consumer->getKey();

        if ($accessToken) {
            $query['oauth_token'] = $accessToken->getToken();
        }

        $query['oauth_signature_method'] = $this->signature->getName();
        $query['oauth_signature'] = $this->signature->buildSignature(
            $this->getSignatureBaseString($method, $uri, $query),
            $this->consumer,
            $this->consumerToken
        );

        $headers['Authorization'] = $this->authorizationHeader($query);
    }

    /**
     * @param string $uri
     * @param string $method
     * @param array $query
     * @param array|null $payload
     * @param array $headers
     * @return ResponseInterface
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Exception
     */
    protected function oauthRequest($uri, $method = 'GET', array $query = [], array $payload = null, $headers = []): ResponseInterface
    {
        $headers = array_merge([
            'Accept' => 'application/json'
        ], $headers);

        if ($method === 'POST') {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $query['oauth_version'] = '1.0';
        $query['oauth_nonce'] = $this->generateState();
        $query['oauth_timestamp'] = time();
        $query['oauth_consumer_key'] = $this->consumer->getKey();

        $query['oauth_signature_method'] = $this->signature->getName();
        $query['oauth_signature'] = $this->signature->buildSignature(
            $this->getSignatureBaseString($method, $uri, $query),
            $this->consumer,
            $this->consumerToken
        );

        $headers['Authorization'] = $this->authorizationHeader($query);

        return $this->executeRequest(
            $this->createRequest(
                $method,
                $uri,
                $query,
                $headers,
                $payload
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function makeAuthUrl(): string
    {
        $urlParameters = $this->getAuthUrlParameters();
        $urlParameters['oauth_token'] = $this->requestAuthToken()->getKey();

        return $this->getAuthorizeUri() . '?' . http_build_query($urlParameters, '', '&');
    }

    /**
     * @param array $parameters
     * @return AccessToken
     * @throws InvalidAccessToken
     * @throws InvalidResponse
     * @throws UnknownAuthorization
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAccessTokenByRequestParameters(array $parameters)
    {
        $token = $this->session->get('oauth1_request_token');
        if (!$token) {
            throw new UnknownAuthorization();
        }

        $this->session->delete('oauth1_request_token');

        if (!isset($parameters['oauth_verifier'])) {
            throw new Unauthorized('Unknown oauth_verifier');
        }

        return $this->getAccessToken($token, $parameters['oauth_verifier']);
    }

    /**
     * @param Token $token
     * @param string $oauthVerifier
     * @return AccessToken
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAccessToken(Token $token, string $oauthVerifier)
    {
        $this->consumerToken = $token;

        $parameters = [
            'oauth_consumer_key' => $this->consumer->getKey(),
            'oauth_token' => $token->getKey(),
            'oauth_verifier' => $oauthVerifier
        ];

        $response = $this->oauthRequest(
            $this->getRequestTokenAccessUri(),
            $this->requestTokenMethod,
            $parameters
        );

        return $this->parseAccessToken($response->getBody()->getContents());
    }

    /**
     * Parse AccessToken from response's $body
     *
     * @param string $body
     * @return AccessToken
     * @throws InvalidAccessToken
     * @throws InvalidResponse
     */
    public function parseAccessToken(string $body)
    {
        if (empty($body)) {
            throw new InvalidResponse('Provider response with empty body');
        }

        parse_str($body, $token);

        if ($token) {
            if (!is_array($token)) {
                throw new InvalidAccessToken('Response must be array');
            }

            return new AccessToken($token);
        }

        throw new InvalidAccessToken('Server response with not valid/empty JSON');
    }

    /**
     * {@inheritDoc}
     */
    public function createAccessToken(array $information)
    {
        throw new Unsupported('It\'s usefull to use this method for OAuth1, are you sure?');
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
     * @param Token $token
     */
    public function setConsumerToken(Token $token)
    {
        $this->consumerToken = $token;
    }

    /**
     * @return \SocialConnect\OAuth1\Token
     */
    public function getConsumerToken()
    {
        return $this->consumerToken;
    }
}
