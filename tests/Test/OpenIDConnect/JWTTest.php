<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenIDConnect\JWT;

use DateTime;
use ReflectionClass;
use SocialConnect\OpenIDConnect\Exception\InvalidJWT;
use SocialConnect\OpenIDConnect\JWT;

class JWTTest extends \Test\TestCase
{
    /**
     * @return array
     */
    protected function getJWKSet()
    {
        return [
            [
                'kid' => 'testSigKey',
                'kty' => 'RS256',
                'n' => 'TEST',
                'e' => 'TEST'
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getTestHeader()
    {
        return [
            'alg' => 'RS256',
            'kid' => 'testSigKey'
        ];
    }

    /**
     * @param object $object
     * @param string $name
     * @param array $params
     * @return mixed
     */
    protected static function callProtectedMethod($object, $name, array $params = [])
    {
        $class = new ReflectionClass($object);

        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $params);
    }

    protected function encodeJWT($payload)
    {
        $header = $this->getTestHeader();

        $encodedHeader = json_encode($header);
        $b64Header = base64_encode($encodedHeader);

        $encodedPayload = json_encode($payload);
        $b64Payload = base64_encode($encodedPayload);

        return $b64Header . '.' . $b64Payload . '.' . 'signatureLOL';
    }

    public function testValidateClaimsSuccess()
    {
        $token = new JWT(
            array(
                'nbf' => time(),
                'iat' => time(),
                'exp' => time() + 20,
            ),
            $this->getTestHeader()
        );

        self::callProtectedMethod(
            $token,
            'validateClaims'
        );
    }

    public function testValidateClaimsNbfFail()
    {
        $token = new JWT(
            array(
                'nbf' => $nbf = time() + 10,
                'iat' => time(),
                'exp' => time() + 20,
            ),
            $this->getTestHeader()
        );

        parent::setExpectedException(
            \SocialConnect\OpenIDConnect\Exception\InvalidJWT::class,
            sprintf(
                'nbf (Not Fefore) claim is not valid %s',
                date(DateTime::RFC3339, $nbf)
            )
        );

        self::callProtectedMethod(
            $token,
            'validateClaims'
        );
    }

    public function testValidateClaimsNbfScrew()
    {
        JWT::$screw = 30;

        $token = new JWT(
            array(
                'nbf' => $nbf = time() + 10,
                'iat' => time(),
                'exp' => time() + 20,
            ),
            $this->getTestHeader()
        );

        self::callProtectedMethod(
            $token,
            'validateClaims'
        );

        JWT::$screw = 0;
    }

    public function testValidateClaimsExpFail()
    {
        $token = new JWT(
            array(
                'nbf' => time(),
                'iat' => time(),
                'exp' => $exp = time() - 20,
            ),
            $this->getTestHeader()
        );

        parent::setExpectedException(
            \SocialConnect\OpenIDConnect\Exception\InvalidJWT::class,
            sprintf(
                'exp (Expiration Time) claim is not valid %s',
                date(DateTime::RFC3339, $exp)
            )
        );

        self::callProtectedMethod(
            $token,
            'validateClaims'
        );
    }

    public function testValidateHeaderSuccess()
    {
        $token = new JWT(
            [],
            $this->getTestHeader()
        );

        self::callProtectedMethod(
            $token,
            'validateHeader'
        );
    }

    public function testValidateHeaderNoAlg()
    {
        $token = new JWT(
            [],
            [
                'kid' => 'testSigKey'
            ]
        );

        parent::setExpectedException(
            InvalidJWT::class,
            'No alg inside header'
        );

        self::callProtectedMethod(
            $token,
            'validateHeader'
        );
    }

    public function testValidateHeaderNoKid()
    {
        $token = new JWT(
            [],
            [
                'alg' => 'RS256'
            ]
        );

        parent::setExpectedException(
            InvalidJWT::class,
            'No kid inside header'
        );

        self::callProtectedMethod(
            $token,
            'validateHeader'
        );
    }

    public function testDecodeWrongNumberOfSegments()
    {
        parent::setExpectedException(
            InvalidJWT::class,
            'Wrong number of segments'
        );

        JWT::decode(
            'lol',
            []
        );
    }
}
