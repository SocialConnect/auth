<?php

declare(strict_types=1);

namespace SocialConnect\OpenIDConnect\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Exception\InvalidArgumentException;
use SocialConnect\OpenIDConnect\AccessToken;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\OpenIDConnect\AbstractProvider;
use SocialConnect\Common\HttpStack;
use SocialConnect\Provider\Session\SessionInterface;

class AzureAD extends AbstractProvider
{
    const NAME = 'azure-ad';
    const MS_GRAPH_API = 'https://graph.microsoft.com';

    /**
     * @var string
     */
    private $baseUri;

    public function __construct(HttpStack $httpStack, SessionInterface $session, array $parameters)
    {
        if (!isset($parameters['directoryId'])) {
            throw new \InvalidArgumentException('There is no "baseUri" given in the configuration');
        }

        $this->baseUri = sprintf("https://login.microsoftonline.com/%s/", $parameters['directoryId']);

        parent::__construct($httpStack, $session, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return $this->baseUri . 'oauth2/v2.0/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return $this->baseUri . 'oauth2/v2.0/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getOpenIdUrl()
    {
        return $this->baseUri . 'v2.0/.well-known/openid-configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $headers['Authorization'] = 'Bearer ' . $accessToken->getToken();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function extractIdentity(AccessTokenInterface $accessToken)
    {
        if (!$accessToken instanceof AccessToken) {
            throw new InvalidArgumentException(
                '$accessToken must be instance AccessToken'
            );
        }

        $jwt = $accessToken->getJwt();

        $hydrator = new ArrayHydrator([
            'sub' => 'id',
            'name' => 'username',
            'email' => 'email'
        ]);

        $user = $hydrator->hydrate(new User(), $jwt->getPayload());

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        return $this->extractIdentity($accessToken);
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeInline()
    {
        return implode(' ', $this->scope);
    }
}
