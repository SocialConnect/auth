<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth;

use SocialConnect\Auth\AccessTokenInterface;

abstract class AbstractBaseProvider
{
    /**
     * @var Service
     */
    public $service;

    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * @var array
     */
    protected $scope = array();

    /**
     * @var array
     */
    protected $fields = array();

    /**
     * @param Service $service
     * @param Consumer $consumer
     */
    public function __construct(Service $service, Consumer $consumer)
    {
        $this->service = $service;
        $this->consumer = $consumer;
    }

    /**
     * @return mixed
     */
    protected function getRedirectUri()
    {
        return $this->service->getConfig()['redirectUri'];
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getRedirectUri() . '/' . $this->getName() . '/';
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
     * @return \SocialConnect\Auth\AccessTokenInterface
     */
    abstract public function getAccessTokenByRequestParameters(array $requestParameters);

    /**
     * @return string
     */
    abstract public function makeAuthUrl();

    /**
     * Get current user identity from social network by $accessToken
     *
     * @param AccessTokenInterface $accessToken
     * @return \SocialConnect\Common\Entity\User
     *
     * @throws \SocialConnect\Auth\Provider\Exception\InvalidResponse
     */
    abstract public function getIdentity(AccessTokenInterface $accessToken);

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
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getFieldsInline()
    {
        return implode(',', $this->fields);
    }

    /**
     * @return \SocialConnect\Auth\Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }
}
