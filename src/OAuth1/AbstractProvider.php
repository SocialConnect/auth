<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1;

use Exception;
use SebastianBergmann\GlobalState\RuntimeException;
use SocialConnect\Auth\AbstractBaseProvider;
use SocialConnect\Auth\Consumer;
use SocialConnect\Auth\Provider\Exception\InvalidAccessToken;
use SocialConnect\Auth\Provider\Exception\InvalidResponse;
use SocialConnect\Auth\Service;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;
use SocialConnect\OAuth1\Exception\InvalidRequestToken;
use SocialConnect\OAuth1\Signature\MethodHMACSHA1;

abstract class AbstractProvider extends AbstractBaseProvider
{
    /**
     * @var string
     */
    protected $oauth1Version = '1.0a';

    /**
     * @var string
     */
    protected $requestTokenMethod = Client::POST;

    /**
     * @var array
     */
    protected $requestTokenParams = [];

    /**
     * @var array
     */
    protected $requestTokenHeaders = [];

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
    protected $scope = array();

    /**
     * @param Service $service
     * @param Consumer $consumer
     */
    public function __construct(Service $service, Consumer $consumer)
    {
        parent::__construct($service, $consumer);

        $this->consumerToken = new Token('', '');
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
     * @throws Exception
     */
    protected function requestAuthToken()
    {
        /**
         * OAuth Core 1.0 Revision A: oauth_callback: An absolute URL to which the Service Provider will redirect
         * the User back when the Obtaining User Authorization step is completed.
         *
         * http://oauth.net/core/1.0a/#auth_step1
         */
        if ('1.0a' == $this->oauth1Version) {
            $this->requestTokenParams['oauth_callback'] = $this->getRedirectUrl();
        }

        $response = $this->oauthRequest(
            $this->getRequestTokenUri(),
            $this->requestTokenMethod,
            $this->requestTokenParams,
            $this->requestTokenHeaders
        );

        if ($response->getStatusCode() === 200) {
            return $this->parseToken($response->getBody());
        }

        throw new Exception('Unexpected response code ' . $response->getStatusCode());
    }

    /**
     * Parse Token from response's $body
     *
     * @param string|boolean $body
     * @return Token
     * @throws InvalidRequestToken
     * @throws RuntimeException
     */
    public function parseToken($body)
    {
        if (!is_string($body)) {
            throw new RuntimeException('Request $body is not a string, passed: ' . var_export($body, true));
        }

        parse_str($body, $token);
        if (!is_array($token) || !isset($token['oauth_token']) || !isset($token['oauth_token_secret'])) {
            throw new InvalidRequestToken;
        }

        return new Token($token['oauth_token'], $token['oauth_token_secret']);
    }

    /**
     * @param string $uri
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @return \SocialConnect\Common\Http\Response
     */
    protected function oauthRequest($uri, $method = 'GET', $parameters = [], $headers = [])
    {
        $request = Request::fromConsumerAndToken(
            $this->consumer,
            $this->consumerToken,
            $method,
            $uri,
            $parameters
        );

        $request->signRequest(
            new MethodHMACSHA1(),
            $this->consumer,
            $this->consumerToken
        );

        $parameters = array_merge($parameters, $request->parameters);
        $headers = array_replace($request->toHeader(), (array)$headers);

        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        $response = $this->service->getHttpClient()->request(
            $request->getNormalizedHttpUrl(),
            $parameters,
            $method,
            $headers
        );

        return $response;
    }

    /**
     * @return string
     */
    public function makeAuthUrl()
    {
        $urlParameters = [
            'oauth_token' => $this->requestAuthToken()->getKey()
        ];

        return $this->getAuthorizeUri() . '?' . http_build_query($urlParameters, '', '&');
    }

    /**
     * @param array $parameters
     * @return AccessToken
     */
    public function getAccessTokenByRequestParameters(array $parameters)
    {
        $token = new Token($parameters['oauth_token'], '');
        return $this->getAccessToken($token, $parameters['oauth_verifier']);
    }

    /**
     * @param Token $token
     * @param $oauthVerifier
     * @return AccessToken
     * @throws Exception
     */
    public function getAccessToken(Token $token, $oauthVerifier)
    {
        $this->consumerToken = $token;

        $parameters = $this->requestTokenParams;
        $parameters['oauth_verifier'] = $oauthVerifier;

        $response = $this->oauthRequest(
            $this->getRequestTokenAccessUri(),
            $this->requestTokenMethod,
            $parameters,
            $this->requestTokenHeaders
        );

        if ($response->getStatusCode() === 200) {
            return $this->parseAccessToken($response->getBody());
        }

        throw new InvalidResponse(
            'Unexpected response code',
            $response->getBody()
        );
    }

    /**
     * Parse AccessToken from response's $body
     *
     * @param string|boolean $body
     * @return AccessToken
     * @throws InvalidAccessToken
     * @throws RuntimeException
     */
    public function parseAccessToken($body)
    {
        if (!is_string($body)) {
            throw new RuntimeException('Request $body is not a string, passed: ' . var_export($body, true));
        }

        parse_str($body, $token);
        if (!is_array($token) || !isset($token['oauth_token']) || !isset($token['oauth_token_secret'])) {
            throw new InvalidAccessToken;
        }

        $accessToken = new AccessToken($token['oauth_token'], $token['oauth_token_secret']);
        if (isset($token['user_id'])) {
            $accessToken->setUserId($token['user_id']);
        }

        return $accessToken;
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
}
