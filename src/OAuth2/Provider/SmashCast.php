<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use Psr\Http\Message\RequestInterface;
use SocialConnect\Common\Http\Request;
use SocialConnect\OAuth2\Exception\InvalidState;
use SocialConnect\OAuth2\Exception\Unauthorized;
use SocialConnect\OAuth2\Exception\UnknownAuthorization;
use SocialConnect\OAuth2\Exception\UnknownState;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class SmashCast extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'smashcast';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.smashcast.tv/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://api.smashcast.tv/oauth/login';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://api.smashcast.tv/oauth/exchange';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthUrlParameters(): array
    {
        $parameters = $this->getArrayOption('auth.parameters', []);

        // Because SmashCast developers dont know about OAuth spec...
        $parameters['app_token'] = $this->consumer->getKey();
        $parameters['redirect_uri'] = $this->getRedirectUrl();
        $parameters['response_type'] = 'code';

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    protected function makeAccessTokenRequest(string $code): RequestInterface
    {
        $parameters = [
            'request_token' => $code,
            'app_token' => $this->consumer->getKey(),
            'hash' => base64_encode($this->consumer->getKey() . $this->consumer->getSecret()),
        ];


        return new Request(
            $this->requestHttpMethod,
            $this->getRequestTokenUri(),
            [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            http_build_query($parameters)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenByRequestParameters(array $parameters)
    {
        if (isset($parameters['error']) && $parameters['error'] === 'access_denied') {
            throw new Unauthorized();
        }

        if (!$this->getBoolOption('stateless', false)) {
            $state = $this->session->get('oauth2_state');
            if (!$state) {
                throw new UnknownAuthorization();
            }

            if (!isset($parameters['state'])) {
                throw new UnknownState();
            }

            if ($state !== $parameters['state']) {
                throw new InvalidState();
            }
        }

        if (isset($parameters['authToken'])) {
            return new AccessToken(['access_token' => $parameters['authToken']]);
        }

        return $this->getAccessToken($parameters['request_token']);
    }

    /**
     * This method it needed, because I cannot fix auth/login with accessToken
     * BTW: Yes, I known that it's unneeded round trip to the server
     *
     * @param AccessTokenInterface $accessToken
     * @return mixed
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function getUserNameByToken(AccessTokenInterface $accessToken): string
    {
        $response = $this->request('userfromtoken/' . $accessToken->getToken(), [], $accessToken);

        return $response->user_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        // @todo Find a problem with this code
        /*$response = $this->httpClient->request(
            $this->getBaseUri() . 'auth/login',
            [
                'app' => 'desktop', // @any app name, not working, I was using JSON and not working..
                'authToken' => $accessToken->getToken()
            ],
            'POST',
            [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        );*/

        $username = $this->getUserNameByToken($accessToken);

        $response = $this->httpClient->request(
            $this->getBaseUri() . 'user/' . $username,
            [
                'authToken' => $accessToken->getToken()
            ]
        );

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

        $hydrator = new ObjectMap(
            [
                'user_id' => 'id',
                'user_name' => 'username',
                'user_email' => 'email',
            ]
        );

        return $hydrator->hydrate(new User(), $result);
    }
}
