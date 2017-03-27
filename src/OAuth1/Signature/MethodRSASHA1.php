<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1\Signature;

use SocialConnect\Provider\Consumer;
use SocialConnect\OAuth1\Request;
use SocialConnect\OAuth1\Token;
use SocialConnect\OAuth1\Util;

class MethodRSASHA1 extends AbstractSignatureMethod
{
    private $private_key;

    public function __construct($private_key)
    {
        if (! is_readable($private_key)) {
            throw new \InvalidArgumentException('The private key is not readable');
        }

        if (! function_exists('openssl_pkey_get_private')) {
            throw new \InvalidArgumentException('The OpenSSL-Extension seems not to be available. That is necessary to handle RSA-SHA1');
        }

        $this->private_key = $private_key;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'RSA-SHA1';
    }

    /**
     * @param Request $request
     * @param Consumer $consumer
     * @param Token $token
     * @return string
     */
    public function buildSignature(Request $request, Consumer $consumer, Token $token)
    {
        $signatureBase = $request->getSignatureBaseString();
        $parts = [$consumer->getSecret(), null !== $token ? $token->getSecret() : ''];

        $parts = Util::urlencodeRFC3986($parts);
        $key = implode('&', $parts);

        $certificate = openssl_pkey_get_private('file://' . $this->private_key);
        $privateKeyId = openssl_get_privatekey($certificate);
        $signature = null;
        openssl_sign($signatureBase, $signature, $privateKeyId);
        openssl_free_key($privateKeyId);

        return base64_encode($signature);
    }
}
