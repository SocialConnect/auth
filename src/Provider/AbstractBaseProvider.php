<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

use SocialConnect\Auth\Service;

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
     * @return \SocialConnect\Auth\Provider\Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }
}
