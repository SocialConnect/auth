<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

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
            $token->setUid($result['x_mailru_vid']);

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
    public function prepareRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
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

        $response = $this->request('', $query, $accessToken);

        $hydrator = new ObjectMap(
            [
                'uid' => 'id',
                'first_name' => 'firstname',
                'last_name' => 'lastname',
                'nick' => 'username',
                'pic_big' => 'pictureURL'
            ]
        );

        $user = $hydrator->hydrate(new User(), $response[0]);

        if ($user->sex) {
            $user->sex = $user->sex === 1 ? 'female' : 'male';
        }

        return $user;
    }
}
