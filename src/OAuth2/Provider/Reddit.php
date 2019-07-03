<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use Psr\Http\Message\RequestInterface;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

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

        return new \GuzzleHttp\Psr7\Request(
            $this->requestHttpMethod,
            $this->getRequestTokenUri(),
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($this->consumer->getKey() . ':' . $this->consumer->getSecret())
            ],
            http_build_query($parameters)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
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
        $response = $this->request('me.json', [], $accessToken);

        $hydrator = new ObjectMap([]);

        return $hydrator->hydrate(new User(), $response);
    }
}
