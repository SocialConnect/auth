<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider\OAuth1;

use Exception;
use SocialConnect\Auth\InvalidAccessToken;
use SocialConnect\Auth\OAuth\Consumer;
use SocialConnect\Auth\OAuth\Request;
use SocialConnect\Auth\OAuth\SignatureMethodHMACSHA1;
use SocialConnect\Auth\OAuth\Token;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;

abstract class AbstractProvider
{
    /**
     * @var \SocialConnect\Auth\Service
     */
    public $service;

    protected $applicationId;

    protected $applicationSecret;

    protected $scope = array();

    public function __construct(\SocialConnect\Auth\Service $service)
    {
        $this->service = $service;
    }

    protected function getRedirectUri()
    {
        return $this->service->getConfig()['redirectUri'];
    }

    public function getRedirectUrl()
    {
        return $this->getRedirectUri() . '/' . $this->getName() . '/';
    }

    /**
     * @return string
     */
    abstract public function getBaseUri();

    /**
     * @return string
     */
    abstract public function getAuthorizeUri();

    /**
     * @return string
     */
    abstract public function getRequestTokenUrl();

    /**
     * @return string
     */
    abstract public function getRequestTokenAccessUrl();

    /**
     * Return Provider's name
     *
     * @return string
     */
    abstract public function getName();

    protected $oauth1Version = '1.0a';

    /**
     * @var string
     */
    protected $requestTokenMethod = 'POST';

    /**
     * @var array
     */
    protected $requestTokenParameters = [];

    /**
     * @var array
     */
    protected $requestTokenHeaders = [];

    /**
     * @var Consumer
     */
    protected $consumerKey = null;

    /**
     * @var Token
     */
    protected $consumerToken = null;

    protected function requestAuthToken()
    {
        /**
         * OAuth Core 1.0 Revision A: oauth_callback: An absolute URL to which the Service Provider will redirect
         * the User back when the Obtaining User Authorization step is completed.
         *
         * http://oauth.net/core/1.0a/#auth_step1
         */
        if ('1.0a' == $this->oauth1Version) {
            $this->requestTokenParameters['oauth_callback'] = $this->getRedirectUrl();
        }

        $this->consumerKey = new Consumer($this->applicationId, $this->applicationSecret);
        $this->consumerToken = new Token('', '');

        $response = $this->oauthRequest(
            $this->getRequestTokenUrl(),
            $this->requestTokenMethod,
            $this->requestTokenParameters,
            $this->requestTokenHeaders
        );

        if ($response->getStatusCode() === 200) {
            parse_str($response->getBody(), $token);
            return new Token($token['oauth_token'], $token['oauth_token_secret']);
        }

        throw new Exception('Unexpected response code ' . $response->getStatusCode());
    }

    protected function oauthRequest($uri, $method = 'GET', $parameters = [], $headers = [])
    {
        $request = Request::fromConsumerAndToken(
            $this->consumerKey,
            $this->consumerToken,
            $method,
            $uri,
            $parameters
        );

        $request->signRequest(
            new SignatureMethodHMACSHA1(),
            $this->consumerKey,
            $this->consumerToken
        );

        $uri = $request->getNormalizedHttpUrl();
        $parameters = array_merge($parameters, $request->parameters);
        $headers = array_replace($request->toHeader(), (array)$headers);

        $this->service->getHttpClient()->setOption(CURLOPT_ENCODING, 'gzip');
        $this->service->getHttpClient()->setOption(CURLOPT_SSL_VERIFYPEER, true);
        $this->service->getHttpClient()->setOption(CURLOPT_SSL_VERIFYHOST, 2);
        $this->service->getHttpClient()->setOption(CURLOPT_HEADER, true);
        $this->service->getHttpClient()->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);


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
            'oauth_token' => $this->requestAuthToken()->key
        ];

        return $this->getAuthorizeUrl() . '?' . http_build_query($urlParameters, '', '&');
    }

    public function parseToken($body)
    {
        parse_str($body, $token);

        if (!is_array($token) || !isset($token['oauth_token']) || !isset($token['oauth_token_secret'])) {
            throw new InvalidAccessToken('Provider API returned an unexpected response');
        }

        return new Token($token['oauth_token'], $token['oauth_token_secret']);
    }

    /**
     * @param array $parameters
     * @return AccessToken
     */
    public function getAccessTokenByRequestParameters(array $parameters)
    {
        $this->consumerToken = new Token($parameters['oauth_token'], '');
        return $this->getAccessToken($this->consumerToken, $parameters['oauth_verifier']);
    }

    /**
     * @param Token $token
     * @param $oauthVerifier
     * @return Token
     * @throws Exception
     */
    public function getAccessToken(Token $token, $oauthVerifier)
    {
        $this->consumerKey = new Consumer($this->applicationId, $this->applicationSecret);

        $parameters = $this->requestTokenParameters;
        $parameters['oauth_verifier'] = $oauthVerifier;

        $response = $this->oauthRequest(
            $this->getRequestTokenAccessUrl(),
            $this->requestTokenMethod,
            $parameters,
            $this->requestTokenHeaders
        );

        if ($response->getStatusCode() === 200) {
            parse_str($response->getBody(), $token);
            $accessToken = new AccessToken($token['oauth_token'], $token['oauth_token_secret']);

            if (isset($token['user_id'])) {
                $accessToken->setUserId($token['user_id']);
            }

            return $accessToken;
        }

        throw new Exception('Unexpected response code ' . $response->getStatusCode());
    }

    /**
     * Get current user identity from social network by $accessToken
     *
     * @param AccessToken $accessToken
     * @return User
     */
    abstract public function getIdentity(AccessToken $accessToken);

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
     * @param mixed $applicationId
     */
    public function setApplicationId($applicationId)
    {
        $this->applicationId = $applicationId;
    }

    /**
     * @param mixed $applicationSecret
     */
    public function setApplicationSecret($applicationSecret)
    {
        $this->applicationSecret = $applicationSecret;
    }
}
