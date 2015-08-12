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
        return "HMAC-SHA1";
    }
    public function build_signature($request, $consumer, $token)
    {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;
        $key_parts = array($consumer->secret, $token ? $token->secret : '' );
        $key_parts = Util::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);

        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }
}