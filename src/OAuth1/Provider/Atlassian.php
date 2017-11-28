<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace SocialConnect\OAuth1\Provider;

use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\OAuth1\Signature\MethodRSASHA1;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth1\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;
use SocialConnect\Provider\Session\SessionInterface;

class Atlassian extends AbstractProvider
{
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
        return 'atlassian';
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
     * @param \SocialConnect\Common\Http\Client\ClientInterface $httpClient
     * @param \SocialConnect\Provider\Session\SessionInterface  $session
     * @param \SocialConnect\Provider\Consumer           $consumer
     * @param array                                             $parameters
     */
    public function __construct(ClientInterface $httpClient, SessionInterface $session, Consumer $consumer, array $parameters)
    {
        if (!isset($parameters['baseUri'])) {
            throw new \InvalidArgumentException('There is no "baseUri" given in the configuration');
        }
        $this->baseUri = $parameters['baseUri'];
        if (($lastSlash = strrpos($this->baseUri, '/')) == strlen($this->baseUri) - 1) {
            $this->baseUri = substr($this->baseUri, 0, $lastSlash);
        }

        parent::__construct($httpClient, $session, $consumer, $parameters);

        // needs to be set after calling the parent constructor as there the
        // signature is set as well.
        $this->signature = new MethodRSASHA1($consumer->getSecret());
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $this->consumerToken = $accessToken;

        $parameters = [
            'oauth_consumer_key' => $this->consumer->getKey(),
            'oauth_token' => $this->consumerToken->getToken(),
        ];

        $response = $this->oauthRequest(
            $this->getBaseUri() . '/rest/prototype/1/user/current',
            Client::GET,
            $parameters
        );

        $redirectMax = 30;
        $redirectCount = 0;
        while ($response->hasHeader('Location')) {
            if ($redirectMax < $redirectCount++) {
                throw new \RangeException('Too many redirects');
            }

            $response = $this->oauthRequest(
                $response->getHeader('Location'),
                Client::GET,
                $parameters
            );
        }

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $result = $response->json();
        if (!$result) {
            throw new InvalidResponse(
                'API response is not a valid JSON object',
                $response
            );
        }

        if (!isset($result->name) || !$result->name) {
            throw new InvalidResponse(
                'API response without user inside JSON',
                $response
            );
        }

        $hydrator = new ObjectMap([
            'name' => 'username',
            'displayName' => 'fullname',
            'displayableEmail' => 'email',
        ]);

        return $hydrator->hydrate(new User(), $result);
    }
}
