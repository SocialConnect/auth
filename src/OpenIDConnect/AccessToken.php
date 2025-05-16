<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OpenIDConnect;

use SocialConnect\JWX\JWT;
use SocialConnect\Provider\Exception\InvalidAccessToken;

class AccessToken extends \SocialConnect\OAuth2\AccessToken
{
    /**
     * @var JWT
     */
    protected $jwt;

    /**
     * @var string
     */
    protected $idToken;

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

        $this->idToken = $token['id_token'];
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
        $payload = $jwt->getPayload();

        if (isset($payload['sub'])) {
            $this->uid = (string) $payload['sub'];
        }

        $this->jwt = $jwt;
    }

    /**
     * @return string
     */
    public function getIdToken()
    {
        return $this->idToken;
    }
}
