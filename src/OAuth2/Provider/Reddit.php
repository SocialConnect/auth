<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use Psr\Http\Message\RequestInterface;
use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Common\Entity\User;

class Reddit extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'reddit';

    public function getBaseUri()
    {
        return 'https://oauth.reddit.com/api/v1/';
    }

    public function getAuthorizeUri()
    {
        return 'https://ssl.reddit.com/api/v1/authorize';
    }

    public function getRequestTokenUri()
    {
        return 'https://ssl.reddit.com/api/v1/access_token';
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    protected function makeAccessTokenRequest(string $code): RequestInterface
    {
        $parameters = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUrl()
        ];

        return $this->httpStack->createRequest($this->requestHttpMethod, $this->getRequestTokenUri())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization', 'Basic ' . base64_encode($this->consumer->getKey() . ':' . $this->consumer->getSecret()))
            ->withBody($this->httpStack->createStream(http_build_query($parameters)))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken->getToken()}";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('GET', 'me.json', [], $accessToken);

        $hydrator = new ArrayHydrator([]);

        return $hydrator->hydrate(new User(), $response);
    }
}
