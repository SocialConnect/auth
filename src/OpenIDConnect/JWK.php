<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */


namespace SocialConnect\OpenIDConnect;

use SocialConnect\OpenIDConnect\Exception\InvalidJWK;

class JWK
{
    /**
     * @link https://tools.ietf.org/html/rfc7517#section-4.1
     *
     * The "kty" (key type) parameter identifies the cryptographic algorithm
     * family used with the key, such as "RSA" or "EC"
     *
     * @var string
     */
    protected $kty;

    /**
     * @var string
     */
    protected $n;

    /**
     * @var string
     */
    protected $e;

    /**
     * @param array $parameters
     * @throws InvalidJWK
     */
    public function __construct($parameters)
    {
        if (!isset($parameters['kty'])) {
            throw new InvalidJWK('Unknown kty');
        }

        $this->kty = $parameters['kty'];

        if (!isset($parameters['n'])) {
            throw new InvalidJWK('Unknown n');
        }

        $this->n = $parameters['n'];

        if (!isset($parameters['e'])) {
            throw new InvalidJWK('Unknown e');
        }

        $this->e = $parameters['e'];
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        $modulus = JWT::urlsafeB64Decode($this->n);
        $publicExponent = JWT::urlsafeB64Decode($this->e);

        $components = array(
            'modulus' => pack('Ca*a*', 2, self::encodeLength(strlen($modulus)), $modulus),
            'publicExponent' => pack('Ca*a*', 2, self::encodeLength(strlen($publicExponent)), $publicExponent)
        );

        $publicKey = pack(
            'Ca*a*a*',
            48,
            self::encodeLength(strlen($components['modulus']) + strlen($components['publicExponent'])),
            $components['modulus'],
            $components['publicExponent']
        );

        // sequence(oid(1.2.840.113549.1.1.1), null)) = rsaEncryption.
        $rsaOID = pack('H*', '300d06092a864886f70d0101010500'); // hex version of MA0GCSqGSIb3DQEBAQUA
        $publicKey = chr(0) . $publicKey;
        $publicKey = chr(3) . self::encodeLength(strlen($publicKey)) . $publicKey;
        $publicKey = pack(
            'Ca*a*',
            48,
            self::encodeLength(strlen($rsaOID . $publicKey)),
            $rsaOID . $publicKey
        );

        $publicKey = "-----BEGIN PUBLIC KEY-----\r\n" .
            chunk_split(base64_encode($publicKey), 64) .
            '-----END PUBLIC KEY-----';

        return $publicKey;
    }

    /**
     * DER-encode the length
     *
     * DER supports lengths up to (2**8)**127, however, we'll only support lengths up to (2**8)**4.  See
     * {@link http://itu.int/ITU-T/studygroups/com17/languages/X.690-0207.pdf#p=13 X.690 paragraph 8.1.3} for more information.
     *
     * @access private
     * @param int $length
     * @return string
     */
    private static function encodeLength($length)
    {
        if ($length <= 0x7F) {
            return chr($length);
        }

        $temp = ltrim(pack('N', $length), chr(0));
        return pack('Ca*', 0x80 | strlen($temp), $temp);
    }
}
