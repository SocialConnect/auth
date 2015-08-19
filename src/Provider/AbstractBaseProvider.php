<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

use Exception;
use LogicException;
use SocialConnect\Auth\InvalidAccessToken;
use SocialConnect\Auth\OAuth\Request;
use SocialConnect\Auth\OAuth\SignatureMethodHMACSHA1;
use SocialConnect\Auth\OAuth\Token;
use SocialConnect\Auth\Provider\Consumer;
use SocialConnect\Auth\Service;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Http\Client\Client;

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
     * @param Service $service
     * @param \SocialConnect\Auth\Provider\Consumer $consumer
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
     * @return \SocialConnect\Auth\Provider\Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }
}
