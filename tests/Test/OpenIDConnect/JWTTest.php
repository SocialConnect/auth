<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenIDConnect;

use DateTime;
use SocialConnect\OpenIDConnect\Exception\InvalidJWT;
use SocialConnect\OpenIDConnect\JWK;
use SocialConnect\OpenIDConnect\JWT;
use Test\AbstractTestCase;

class JWTTest extends AbstractTestCase
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
    protected function getTestHeader(string $alg = 'RS256', string $kid = 'testSigKey')
    {
        return [
            'alg' => $alg,
            'kid' => $kid
        ];
    }

    protected function encodeJWT(array $payload, array $jwk)
    {
        $header = $this->getTestHeader($jwk['kty'], $jwk['kid']);

        $encodedHeader = json_encode($header);
        $b64Header = base64_encode($encodedHeader);

        $encodedPayload = json_encode($payload);
        $b64Payload = base64_encode($encodedPayload);

        $signature = hash_hmac('SHA512', $b64Header . '.' . $b64Payload, (new JWK($jwk))->getPublicKey(), true);
        $b64Signature = JWT::urlsafeB64Encode($signature);

        return $b64Header . '.' . $b64Payload . '.' . $b64Signature;
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

        // to skip warning
        parent::assertTrue(true);
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

        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage(sprintf(
            'nbf (Not Fefore) claim is not valid %s',
            date(DateTime::RFC3339, $nbf)
        ));

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

        // to skip warning
        parent::assertTrue(true);
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

        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage(
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

        // to skip warning
        parent::assertTrue(true);
    }

    public function testValidateHeaderNoAlg()
    {
        $token = new JWT(
            [],
            [
                'kid' => 'testSigKey'
            ]
        );

        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage('No alg inside header');

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

        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage('No kid inside header');

        self::callProtectedMethod(
            $token,
            'validateHeader'
        );
    }

    public function testDecodeWrongNumberOfSegments()
    {
        parent::expectException(InvalidJWT::class);
        parent::expectExceptionMessage('Wrong number of segments');

        JWT::decode(
            'lol',
            []
        );
    }

    public function testDecodeSuccess()
    {
        $kid = [
            'kid' => 'super-kid-' . time(),
            'kty' => 'HS512',
            'n' => 'test',
            'e' => 'test'
        ];

        $payload = [
            'uid' => '2955b34c-7a3b-4d96-9fd1-2930c18f9989'
        ];

        $jwtAsString =  $this->encodeJWT(
            $payload,
            $kid
        );

        $jwt = JWT::decode(
            $jwtAsString,
            [
                $kid
            ]
        );

        parent::assertSame($payload, $jwt->getPayload());
    }
}
