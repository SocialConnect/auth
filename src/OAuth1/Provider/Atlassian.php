<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth1\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Common\HttpStack;
use SocialConnect\OAuth1\Signature\MethodRSASHA1;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth1\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Provider\Session\SessionInterface;

class Atlassian extends AbstractProvider
{
    const NAME = 'atlassian';

    /**
     * @var string The Base-URI of the Atlassian instance
     */
    private $baseUri;

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
        return $this->getBaseUri() . '/plugins/servlet/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return $this->getBaseUri() . '/plugins/servlet/oauth/request-token';
    }

    /**
     * @return string
     */
    public function getRequestTokenAccessUri()
    {
        return $this->getBaseUri() . '/plugins/servlet/oauth/access-token';
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Atlassian constructor.
     *
     * Required configuration parameters are:
     *
     * * baseUri The base URI of your self-hosted atlassian product
     * * applicationSecret The path to the private key file used for signing.
     * * applicationId The ID shared with your Atlassian instance
     *
     * @param HttpStack $httpStack
     * @param \SocialConnect\Provider\Session\SessionInterface $session
     * @param array $parameters
     * @throws \SocialConnect\Provider\Exception\InvalidProviderConfiguration
     */
    public function __construct(HttpStack $httpStack, SessionInterface $session, array $parameters)
    {
        if (!isset($parameters['baseUri'])) {
            throw new \InvalidArgumentException('There is no "baseUri" given in the configuration');
        }

        $this->baseUri = $parameters['baseUri'];

        if (($lastSlash = strrpos($this->baseUri, '/')) == strlen($this->baseUri) - 1) {
            $this->baseUri = substr($this->baseUri, 0, $lastSlash);
        }

        parent::__construct($httpStack, $session, $parameters);

        // needs to be set after calling the parent constructor as there the
        // signature is set as well.
        $this->signature = new MethodRSASHA1($this->consumer->getSecret());
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $parameters = [
            'oauth_consumer_key' => $this->consumer->getKey(),
            'oauth_token' => $accessToken->getToken(),
        ];

        $response = $this->oauthRequest(
            $this->getBaseUri() . '/rest/prototype/1/user/current',
            'GET',
            $parameters
        );

        $redirectMax = 30;
        $redirectCount = 0;

        while ($response->hasHeader('Location')) {
            if ($redirectMax < $redirectCount++) {
                throw new \RangeException('Too many redirects');
            }

            $response = $this->oauthRequest(
                $response->getHeaderLine('Location'),
                'GET',
                $parameters
            );
        }

        $result = $this->hydrateResponse($response);

        if (!isset($result['name']) || !$result['name']) {
            throw new InvalidResponse(
                'API response without user inside JSON',
                $response
            );
        }

        $hydrator = new ArrayHydrator([
            'name' => 'username',
            'displayName' => 'fullname',
            'displayableEmail' => 'email',
        ]);

        return $hydrator->hydrate(new User(), $result);
    }
}
