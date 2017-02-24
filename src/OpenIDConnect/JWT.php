<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OpenIDConnect;

use DateTime;
use SocialConnect\OpenIDConnect\Exception\InvalidJWT;
use SocialConnect\OpenIDConnect\Exception\UnsupportedSignatureAlgoritm;

class JWT
{
    /**
     * When checking nbf, iat or exp
     * we provide additional time screw/leeway
     *
     * @link https://github.com/SocialConnect/auth/issues/26
     */
    public static $screw = 0;

    /**
     * Map of supported algorithms
     *
     * @var array
     */
    public static $algorithms = array(
        // HS
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'HS512' => ['hash_hmac', 'SHA512'],
        // RS
        'RS256' => ['openssl', 'SHA256'],
        'RS384' => ['openssl', 'SHA384'],
        'RS512' => ['openssl', 'SHA512'],
    );

    /**
     * @var array
     */
    protected $header;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var string|null
     */
    protected $signature;

    /**
     * @param string $input
     * @return string
     */
    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;

        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }

        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * @param array $payload
     * @param array $header
     * @param string|null $signature
     */
    public function __construct(array $payload, array $header, $signature = null)
    {
        $this->payload = $payload;
        $this->header = $header;
        $this->signature = $signature;
    }

    /**
     * @param string $token
     * @param array $keys
     * @return JWT
     * @throws InvalidJWT
     */
    public static function decode($token, array $keys)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new InvalidJWT('Wrong number of segments');
        }

        list ($header64, $payload64, $signature64) = $parts;

        $headerPayload = base64_decode($header64);
        if (!$headerPayload) {
            throw new InvalidJWT('Cannot decode base64 from header');
        }

        $header = json_decode($headerPayload, true);
        if ($header === null) {
            throw new InvalidJWT('Cannot decode JSON from header');
        }

        $decodedPayload = base64_decode($payload64);
        if (!$decodedPayload) {
            throw new InvalidJWT('Cannot decode base64 from payload');
        }

        $payload = json_decode($decodedPayload, true);
        if ($payload === null) {
            throw new InvalidJWT('Cannot decode JSON from payload');
        }

        $token = new self($payload, $header, self::urlsafeB64Decode($signature64));
        $token->validate("{$header64}.{$payload64}", $keys);

        return $token;
    }

    protected function validateHeader()
    {
        if (!isset($this->header['alg'])) {
            throw new InvalidJWT('No alg inside header');
        }

        if (!isset($this->header['kid'])) {
            throw new InvalidJWT('No kid inside header');
        }
    }

    protected function validateClaims()
    {
        $now = time();

        /**
         * @link https://tools.ietf.org/html/rfc7519#section-4.1.5
         * "nbf" (Not Before) Claim check
         */
        if (isset($this->payload['nbf']) && $this->payload['nbf'] > ($now + self::$screw)) {
            throw new InvalidJWT(
                'nbf (Not Fefore) claim is not valid ' . date(DateTime::RFC3339, $this->payload['nbf'])
            );
        }

        /**
         * @link https://tools.ietf.org/html/rfc7519#section-4.1.6
         * "iat" (Issued At) Claim
         */
        if (isset($this->payload['iat']) && $this->payload['iat'] > ($now + self::$screw)) {
            throw new InvalidJWT(
                'iat (Issued At) claim is not valid ' . date(DateTime::RFC3339, $this->payload['iat'])
            );
        }

        /**
         * @link https://tools.ietf.org/html/rfc7519#section-4.1.4
         * "exp" (Expiration Time) Claim
         */
        if (isset($this->payload['exp']) && ($now - self::$screw) >= $this->payload['exp']) {
            throw new InvalidJWT(
                'exp (Expiration Time) claim is not valid ' . date(DateTime::RFC3339, $this->payload['exp'])
            );
        }
    }

    /**
     * @param string $data
     * @param array $keys
     * @throws InvalidJWT
     */
    protected function validate($data, array $keys)
    {
        $this->validateHeader();
        $this->validateClaims();

        $result = $this->verifySignature($data, $keys);
        if (!$result) {
            throw new InvalidJWT('Unexpected signature');
        }
    }

    /**
     * @param array $keys
     * @param string $kid
     * @return JWK
     * @throws \RuntimeException
     */
    protected function findKeyByKind(array $keys, $kid)
    {
        foreach ($keys as $key) {
            if ($key['kid'] === $kid) {
                return new JWK($key);
            }
        }

        throw new \RuntimeException('Unknown key');
    }

    /**
     * @param string $data
     * @param array $keys
     * @return bool
     * @throws UnsupportedSignatureAlgoritm
     */
    protected function verifySignature($data, array $keys)
    {
        $supported = isset(self::$algorithms[$this->header['alg']]);
        if (!$supported) {
            throw new UnsupportedSignatureAlgoritm($this->header['alg']);
        }

        $jwk = $this->findKeyByKind($keys, $this->header['kid']);

        list ($function, $signatureAlg) = self::$algorithms[$this->header['alg']];
        switch ($function) {
            case 'openssl':
                if (!function_exists('openssl_verify')) {
                    throw new \RuntimeException('Openssl-ext is required to use RS encryption.');
                }

                $result = openssl_verify(
                    $data,
                    $this->signature,
                    $jwk->getPublicKey(),
                    $signatureAlg
                );
                
                return $result == 1;
            case 'hash_hmac':
                if (!function_exists('hash_hmac')) {
                    throw new \RuntimeException('hash-ext is required to use HS encryption.');
                }

                $hash = hash_hmac($signatureAlg, $data, $jwk->getPublicKey(), true);

                /**
                 * @todo In SocialConnect/Auth 2.0 drop PHP 5.5 support and support for hash_equals emulation
                 */
                if (function_exists('hash_equals')) {
                    return hash_equals($this->signature, $hash);
                }

                if (strlen($this->signature) != strlen($hash)) {
                    return false;
                }

                $ret = 0;
                $res = $this->signature ^ $hash;

                for ($i = strlen($res) - 1; $i >= 0; $i--) {
                    $ret |= ord($res[$i]);
                }

                return !$ret;
        }

        throw new UnsupportedSignatureAlgoritm($this->header['alg']);
    }
}
