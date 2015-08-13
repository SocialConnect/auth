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

abstract class Provider
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
    abstract public function getAuthorizeUrl();

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

        return $response;
    }

    protected function oauthRequest($uri, $method = 'GET', $parameters = [], $headers = [])
    {
        $request = Request::from_consumer_and_token(
            $this->consumerKey,
            $this->consumerToken,
            $method,
            $uri,
            $parameters
        );

        $request->sign_request(
            new SignatureMethodHMACSHA1(),
            $this->consumerKey,
            $this->consumerToken
        );

        $uri        = $request->get_normalized_http_url();
        $parameters = $request->parameters;
        $headers    = array_replace($request->to_header(), (array) $headers);

        $this->service->getHttpClient()->setOption(CURLOPT_ENCODING, 'gzip');
        $this->service->getHttpClient()->setOption(CURLOPT_SSL_VERIFYPEER, true);
        $this->service->getHttpClient()->setOption(CURLOPT_SSL_VERIFYHOST, 2);
        $this->service->getHttpClient()->setOption(CURLOPT_HEADER, true);
        $this->service->getHttpClient()->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);


        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        $response = $this->service->getHttpClient()->request(
            $request->get_normalized_http_url(),
            [],
            $method,
            $headers
        );

        if ($response->getStatusCode() === 200) {
            parse_str($response->getBody(), $token);
            return new Token($token['oauth_token'], $token['oauth_token_secret']);
        }

        throw new Exception('Unexpected response code ' . $response->getStatusCode());
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
     * @param string $code
     * @return AccessToken
     */
    public function getAccessToken(array $parameters)
    {
        $this->oauthRequest(
            $this->getRequestTokenAccessUrl(),
            $this->requestTokenMethod,
            $parameters,
            $this->requestTokenHeaders
        );

//        if (!is_string($code)) {
//            throw new \InvalidArgumentException('Parameter $code must be a string');
//        }
//
//        $parameters = array(
//            'client_id' => $this->applicationId,
//            'client_secret' => $this->applicationSecret,
//            'code' => $code,
//            'grant_type' => 'authorization_code',
//            'redirect_uri' => $this->getRedirectUrl()
//        );
//
//        $response = $this->service->getHttpClient()->request($this->getRequestTokenUri() . '?' . http_build_query($parameters), array(), Client::POST);
//        $body = $response->getBody();
//        var_dump($body);
//        die();
//
//        return $this->parseToken($body);
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
