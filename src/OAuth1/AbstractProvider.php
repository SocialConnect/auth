<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1;

use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\OAuth1\Exception\UnknownAuthorization;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Http\Client\Client;
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
    protected $requestTokenMethod = Client::POST;

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
     * @var  \SocialConnect\OAuth1\Signature\AbstractSignatureMethod
     */
    protected $signature;

    /**
     * @param ClientInterface $httpClient
     * @param Consumer $consumer
     * @param array $parameters
     */
    public function __construct(ClientInterface $httpClient, SessionInterface $session, Consumer $consumer, array $parameters)
    {
        parent::__construct($httpClient, $session, $consumer, $parameters);

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
     * @throws InvalidResponse
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
        if ('1.0a' == $this->oauth1Version) {
            $parameters['oauth_callback'] = $this->getRedirectUrl();
        }

        $response = $this->oauthRequest(
            $this->getRequestTokenUri(),
            $this->requestTokenMethod,
            $parameters
        );

        if ($response->isSuccess()) {
            $token = $this->parseToken($response->getBody());


            $this->session->set('oauth1_request_token', $token);

            return $token;
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
     * @return \SocialConnect\Common\Http\Response
     */
    public function oauthRequest($uri, $method = Client::GET, $parameters = [], $headers = [])
    {
        $headers = array_merge([
            'Accept' => 'application/json'
        ], $headers);

        if ($method == Client::POST) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $parameters = array_merge(
            [
                'oauth_version' => '1.0',
                'oauth_nonce' => md5(time() . mt_rand()),
                'oauth_timestamp' => time(),
                'oauth_consumer_key' => $this->consumer->getKey()
            ],
            $parameters
        );

        $request = new Request(
            $uri,
            $parameters,
            $method,
            $headers
        );

        $request->signRequest(
            $this->signature,
            $this->consumer,
            $this->consumerToken
        );

        return $this->httpClient->fromRequest($request);
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
     * @throws \SocialConnect\OAuth1\Exception\UnknownAuthorization
     */
    public function getAccessTokenByRequestParameters(array $parameters)
    {
        $token = $this->session->get('oauth1_request_token');
        if (!$token) {
            throw new UnknownAuthorization();
        }

        $this->session->delete('oauth1_request_token');

        return $this->getAccessToken($token, $parameters['oauth_verifier']);
    }

    /**
     * @param Token $token
     * @param $oauthVerifier
     * @return AccessToken
     * @throws InvalidResponse
     */
    public function getAccessToken(Token $token, $oauthVerifier)
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

        if ($response->getStatusCode() === 200) {
            return $this->parseAccessToken($response->getBody());
        }

        throw new InvalidResponse(
            'Unexpected response code',
            $response
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
