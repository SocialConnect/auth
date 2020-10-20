<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Consumer;
use SocialConnect\Common\Entity\User;

/**
 * @link https://apiok.ru/dev
 */
class Odnoklassniki extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'odnoklassniki';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.ok.ru/api/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'http://connect.ok.ru/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'http://api.odnoklassniki.ru/oauth/token.do';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @link https://apiok.ru/dev/methods/
     *
     * @param array $requestParameters
     * @param AccessTokenInterface $accessToken
     * @return string
     */
    protected function makeSecureSignature(array $requestParameters, AccessTokenInterface $accessToken)
    {
        ksort($requestParameters);

        $params = '';

        foreach ($requestParameters as $key => $value) {
            if ($key === 'access_token') {
                continue;
            }

            $params .= "$key=$value";
        }

        return strtolower(md5($params . md5($accessToken->getToken() . $this->consumer->getSecret())));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $parameters = [
            'application_key' => $this->consumer->getPublic(),
            'access_token' => $accessToken->getToken(),
            'format' => 'json'
        ];

        $parameters['sig'] = $this->makeSecureSignature($parameters, $accessToken);

        $response = $this->request('GET', 'users/getCurrentUser', $parameters, $accessToken);

        $hydrator = new ArrayHydrator([
            'uid' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'name' => 'fullname',
            'pic_3' => 'pictureURL',
            'email' => 'email'
        ]);

        return $hydrator->hydrate(new User(), $response);
    }

    /**
     * {@inheritDoc}
     */
    protected function createConsumer(array $parameters): Consumer
    {
        $consumer = parent::createConsumer($parameters);
        $consumer->setPublic(
            $this->getRequiredStringParameter('applicationPublic', $parameters)
        );

        return $consumer;
    }
}
