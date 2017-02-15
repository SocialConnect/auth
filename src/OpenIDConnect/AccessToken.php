<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OpenIDConnect;

use SocialConnect\Provider\Exception\InvalidAccessToken;

class AccessToken extends \SocialConnect\OAuth2\AccessToken
{
    /**
     * @var JWT
     */
    protected $jwt;

    /**
     * @param array $token
     * @throws InvalidAccessToken
     */
    public function __construct(array $token)
    {
        parent::__construct($token);

        if (!isset($token['id_token'])) {
            throw new InvalidAccessToken('id_token doesnot exists inside AccessToken');
        }
    }

    /**
     * @return JWT
     */
    public function getJwt()
    {
        return $this->jwt;
    }

    /**
     * @param JWT $jwt
     */
    public function setJwt(JWT $jwt)
    {
        $this->jwt = $jwt;
    }
}
