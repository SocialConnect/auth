<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\OAuth;

class SignatureMethodHMACSHA1 extends AbstractSignatureMethod
{
    public function get_name()
    {
        return 'HMAC-SHA1';
    }
    public function build_signature(Request $request, Consumer $consumer, $token)
    {
        $signatureBase = $request->get_signature_base_string();
        $parts = array($consumer->secret, null !== $token ? $token->secret : "");

        $parts = Util::urlencode_rfc3986($parts);
        $key = implode('&', $parts);

        return base64_encode(hash_hmac('sha1', $signatureBase, $key, true));
    }
}