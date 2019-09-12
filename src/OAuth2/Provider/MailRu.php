<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;

class MailRu extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'mail-ru';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://appsmail.ru/platform/api';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://connect.mail.ru/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://connect.mail.ru/oauth/token';
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
    public function parseToken(string $body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $result = json_decode($body, true);
        if ($result) {
            $token = new AccessToken($result);
            $token->setUserId((string) $result['x_mailru_vid']);

            return $token;
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }

    /**
     * Copy/pasted from MailRU examples :)
     *
     * @param array $requestParameters
     * @return string
     */
    protected function makeSecureSignature(array $requestParameters)
    {
        ksort($requestParameters);

        $params = '';

        foreach ($requestParameters as $key => $value) {
            $params .= "$key=$value";
        }

        return md5($params . $this->consumer->getSecret());
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        $query['client_id'] = $this->consumer->getKey();
        $query['format'] = 'json';
        $query['secure'] = 1;

        if ($accessToken) {
            $query['session_key'] = $accessToken->getToken();
            $query['sig'] = $this->makeSecureSignature($query);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $query = [
            'method' => 'users.getInfo',
        ];

        $response = $this->request('GET', '', $query, $accessToken);

        $hydrator = new ArrayHydrator([
            'uid' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'nick' => 'username',
            'pic_big' => 'pictureURL',
            'sex' => static function ($value, User $user) {
                $user->setSex($value === 1 ? User::SEX_FEMALE : User::SEX_MALE);
            }
        ]);

        return $hydrator->hydrate(new User(), $response[0]);
    }
}
