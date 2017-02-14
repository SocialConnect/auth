<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OpenIDConnect;

use SocialConnect\OpenIDConnect\Exception\InvalidJWT;
use SocialConnect\OpenIDConnect\Exception\UnsupportedSignatureAlgoritm;

class JWT
{
    /**
     * Map of supported algorithms
     *
     * @var array
     */
    public static $algorithms = array(
        // HS
        'HS256' => ['hash_hmac', MHASH_SHA256],
        'HS384' => ['hash_hmac', MHASH_SHA384],
        'HS512' => ['hash_hmac', MHASH_SHA512],
        // RS
        'RS256' => ['openssl', OPENSSL_ALGO_SHA256],
        'RS384' => ['openssl', OPENSSL_ALGO_SHA384],
        'RS512' => ['openssl', OPENSSL_ALGO_SHA512],
    );

    /**
     * @var array
     */
    protected $parts;

    /**
     * @var array
     */
    protected $header;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var string
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
     * @param string $token
     * @param array $keys
     * @throws InvalidJWT
     */
    public function __construct($token, array $keys)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new InvalidJWT('Wrong number of segments');
        }

        list ($header64, $payload64, $token64) = $parts;

        $headerPayload = base64_decode($header64, true);
        if (!$headerPayload) {
            throw new InvalidJWT('Cannot decode base64 from header');
        }

        $this->header = json_decode($headerPayload);
        if ($this->header === null) {
            throw new InvalidJWT('Cannot decode JSON from header');
        }

        $decodedPayload = base64_decode($payload64, true);
        if (!$decodedPayload) {
            throw new InvalidJWT('Cannot decode base64 from payload');
        }

        $this->payload = json_decode($decodedPayload);
        if ($this->payload === null) {
            throw new InvalidJWT('Cannot decode JSON from payload');
        }

        $this->signature = self::urlsafeB64Decode($token64);

        $this->validate("{$header64}.{$payload64}", $keys);
    }

    /**
     * @param string $data
     * @param array $keys
     * @throws InvalidJWT
     */
    protected function validate($data, array $keys)
    {
        if (!isset($this->header->alg)) {
            throw new InvalidJWT('No alg inside header');
        }

        if (!isset($this->header->kid)) {
            throw new InvalidJWT('No kid inside header');
        }

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
     * @return bool
     * @throws \RuntimeException
     * @throws \SocialConnect\OpenIDConnect\Exception\UnsupportedSignatureAlgoritm
     */
    protected function verifySignature($data, array $keys)
    {
        $supported = isset(self::$algorithms[$this->header->alg]);
        if (!$supported) {
            throw new UnsupportedSignatureAlgoritm($this->header->alg);
        }

        $jwk = $this->findKeyByKind($keys, $this->header->kid);

        list ($function, $signatureAlg) = self::$algorithms[$this->header->alg];
        switch ($function) {
            case 'openssl':
                if (!function_exists('openssl_verify')) {
                    throw new \RuntimeException('Openssl-ext is required to use RSA encryption.');
                }

                $result = openssl_verify(
                    $data,
                    $this->signature,
                    $jwk->getPublicKey(),
                    $signatureAlg
                );
                
                return $result == 1;
            case 'hash_hmac':
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

                for($i = strlen($res) - 1; $i >= 0; $i--) {
                    $ret |= ord($res[$i]);
                }

                return !$ret;
        }

        throw new UnsupportedSignatureAlgoritm($this->header->alg);
    }
}
