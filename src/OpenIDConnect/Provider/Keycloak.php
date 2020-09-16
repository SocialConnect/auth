<?php

declare(strict_types=1);

namespace SocialConnect\OpenIDConnect\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Exception\InvalidArgumentException;
use SocialConnect\Common\HttpStack;
use SocialConnect\OpenIDConnect\AbstractProvider;
use SocialConnect\OpenIDConnect\AccessToken;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Session\SessionInterface;

class Keycloak extends AbstractProvider
{
    const NAME = 'keycloak';

    protected $name;

    protected $baseUrl;

    protected $realm;

    protected $protocol = 'openid-connect';

    protected $hydrateMapper = [];

    public function __construct(HttpStack $httpStack, SessionInterface $session, array $parameters)
    {
        parent::__construct($httpStack, $session, $parameters);

        $this->name = isset($parameters['name']) ? $parameters['name'] : self::NAME;

        $this->baseUrl = rtrim($this->getRequiredStringParameter('baseUrl', $parameters), '/') . '/';

        $this->realm = $this->getRequiredStringParameter('realm', $parameters);

        if (isset($parameters['protocol'])) {
            $this->protocol = $parameters['protocol'];
        }

        if (isset($parameters['hydrateMapper'])) {
            $this->hydrateMapper = $parameters['hydrateMapper'];
        }
    }

    public function getBaseUri()
    {
        return $this->baseUrl;
    }

    public function getName()
    {
        return $this->name;
    }

    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $headers['Authorization'] = 'Bearer ' . $accessToken->getToken();
        }
    }

    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $url = sprintf('realms/%s/protocol/%s/userinfo', $this->realm, $this->protocol);

        $response = $this->request('GET', $url, [], $accessToken, []);

        $hydrator = new ArrayHydrator($this->hydrateMapper + [
                'sub' => 'id',
                'preferred_username' => 'username',
                'given_name' => 'firstname',
                'family_name' => 'lastname',
                'email' => 'email',
                'email_verified' => 'emailVerified',
                'name' => 'fullname',
                'gender' => static function ($value, User $user) {
                    $user->setSex($value);
                },
                'birthdate' => static function ($value, User $user) {
                    $user->setBirthday(date_create($value, new \DateTimeZone('UTC')));
                },
            ]);

        return $hydrator->hydrate(new User(), $response);
    }

    public function getAuthorizeUri()
    {
        return $this->getBaseUri() . sprintf('realms/%s/protocol/%s/auth', $this->realm, $this->protocol);
    }

    public function getRequestTokenUri()
    {
        return $this->getBaseUri() . sprintf('realms/%s/protocol/%s/token', $this->realm, $this->protocol);
    }

    public function getOpenIdUrl()
    {
        return $this->getBaseUri() . sprintf('realms/%s/.well-known/openid-configuration', $this->realm);
    }

    public function extractIdentity(AccessTokenInterface $accessToken)
    {
        if (!$accessToken instanceof AccessToken) {
            throw new InvalidArgumentException(
                '$accessToken must be instance AccessToken'
            );
        }

        $jwt = $accessToken->getJwt();

        $hydrator = new ArrayHydrator($this->hydrateMapper + [
            'sub' => 'id',
            'preferred_username' => 'username',
            'given_name' => 'firstname',
            'family_name' => 'lastname',
            'email' => 'email',
            'email_verified' => 'emailVerified',
            'name' => 'fullname',
            'gender' => static function ($value, User $user) {
                $user->setSex($value);
            },
            'birthdate' => static function ($value, User $user) {
                $user->setBirthday(date_create($value, new \DateTimeZone('UTC')));
            },
        ]);

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $jwt->getPayload());

        return $user;
    }

    public function getScopeInline()
    {
        $scopes = $this->scope;

        if (!in_array('openid', $scopes)) {
            array_unshift($scopes, 'openid');
        }

        return implode(' ', $scopes);
    }
}
