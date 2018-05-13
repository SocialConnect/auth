<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Provider;

use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\Provider\Session\SessionInterface;

abstract class AbstractBaseProvider
{
    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * @var array
     */
    protected $scope = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param ClientInterface $httpClient
     * @param SessionInterface $session
     * @param array $parameters
     */
    public function __construct(ClientInterface $httpClient, SessionInterface $session, Consumer $consumer, array $parameters)
    {
        $this->httpClient = $httpClient;
        $this->session = $session;
        $this->consumer = $consumer;

        if (isset($parameters['scope'])) {
            $this->setScope($parameters['scope']);
        }

        if (isset($parameters['fields'])) {
            $this->setFields($parameters['fields']);
        }

        if (isset($parameters['redirectUri'])) {
            $this->redirectUri = $parameters['redirectUri'];
        }

        if (isset($parameters['options'])) {
            $this->options = $parameters['options'];
        }
    }

    /**
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public function getBoolOption($key, $default): bool
    {
        if (array_key_exists($key, $this->options)) {
            return (bool) $this->options[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param array $default
     * @return array
     */
    public function getArrayOption($key, array $default = []): array
    {
        if (array_key_exists($key, $this->options)) {
            return (array) $this->options[$key];
        }

        return $default;
    }

    /**
     * @return mixed
     */
    protected function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return str_replace('${provider}', $this->getName(), $this->getRedirectUri());
    }

    /**
     * Default parameters for auth url, can be redeclared inside implementation of the Provider
     *
     * @return array
     */
    public function getAuthUrlParameters(): array
    {
        return $this->getArrayOption('auth.parameters', []);
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
     * @return \SocialConnect\Provider\AccessTokenInterface
     */
    abstract public function getAccessTokenByRequestParameters(array $requestParameters);

    /**
     * @return string
     */
    abstract public function makeAuthUrl(): string;

    /**
     * Get current user identity from social network by $accessToken
     *
     * @param AccessTokenInterface $accessToken
     * @return \SocialConnect\Common\Entity\User
     *
     * @throws \SocialConnect\Provider\Exception\InvalidResponse
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
     * @return \SocialConnect\Provider\Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }
}
