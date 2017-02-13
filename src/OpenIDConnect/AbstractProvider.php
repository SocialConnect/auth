<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OpenIDConnect;

use SocialConnect\Provider\Exception\InvalidAccessToken;

abstract class AbstractProvider extends \SocialConnect\OAuth2\AbstractProvider
{
    /**
     * @return array
     */
    abstract public function getKeys();

    /**
     * Default parameters for auth url, can be redeclared inside implementation of the Provider
     *
     * @return array
     */
    public function getAuthUrlParameters()
    {
        return [
            'client_id' => $this->consumer->getKey(),
            'redirect_uri' => $this->getRedirectUrl(),
            'response_type' => 'code',
            //'response_mode' => 'form_post',
            'scope' => 'openid'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        $result = json_decode($body, true);
        if ($result) {
            $token = new AccessToken($result);
            $token->setJwt(new JWT($result['id_token'], $this->getKeys()));

            return $token;
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }
}
