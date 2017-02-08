<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1;

use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
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
     * @param ClientInterface $httpClient
     * @param Consumer $consumer
     * @param array $parameters
     */
    public function __construct(ClientInterface $httpClient, Consumer $consumer, array $parameters)
    {
        parent::__construct($httpClient, $consumer, $parameters);

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
     * @throws InvalidResponse
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

        if ($response->isSuccess()) {
            return $this->parseToken($response->getBody());
        }

        throw new InvalidResponse('Provider response is not success');
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

        $response = $this->httpClient->request(
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
     * @throws InvalidResponse
     */
    public function parseAccessToken($body)
    {
        if (empty($body)) {
            throw new InvalidResponse('Provider response with empty body');
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
