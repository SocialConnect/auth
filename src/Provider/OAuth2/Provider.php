<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider\OAuth2;

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
        return $this->getRedirectUri() . '?provider=' . $this->getName();
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
    abstract public function getRequestTokenUri();

    /**
     * Return Provider's name
     *
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    public function makeAuthUrl()
    {
        $urlParameters = array(
            'client_id' => $this->applicationId,
            'redirect_uri' => $this->getRedirectUrl()
        );

        if (count($this->scope) > 0) {
            $urlParameters['scope'] = $this->getScopeInline();
        }

        return $this->getAuthorizeUri() . '?' . http_build_query($urlParameters);
    }

    /**
     * @param $body
     * @return AccessToken
     */
    public function parseToken($body)
    {
        parse_str($body, $token);
        return new AccessToken($token['access_token']);
    }

    /**
     * @param $code
     * @return AccessToken
     */
    public function getAccessToken($code)
    {
        $parameters = array(
            'client_id' => $this->applicationId,
            'client_secret' => $this->applicationSecret,
            'code' => $code,
            'redirect_uri' => $this->getRedirectUrl()
        );

        $response = $this->service->getHttpClient()->request($this->getRequestTokenUri() . '?' . http_build_query($parameters));
        $body = $response->getBody();

        return $this->parseToken($body);
    }

    public function begin()
    {

    }

    public function finish()
    {

    }

    public function requestAccessToken()
    {

    }

    public function getClient()
    {

    }

    public function getUserIdentity()
    {

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