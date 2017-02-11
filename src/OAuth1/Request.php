<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1;

use SocialConnect\Provider\Consumer;
use SocialConnect\OAuth1\Signature\AbstractSignatureMethod;

class Request extends \SocialConnect\Common\Http\Request
{
    /**
     * @return string
     */
    public function getSignatureBaseString()
    {
        $parts = [
            $this->method,
            $this->uri,
            $this->getSignableParameters()
        ];

        $parts = Util::urlencodeRFC3986($parts);

        return implode('&', $parts);
    }

    /**
     * @return string
     */
    public function getSignableParameters()
    {
        return Util::buildHttpQuery(
            $this->parameters
        );
    }

    /**
     * @param AbstractSignatureMethod $signatureMethod
     * @param Consumer $consumer
     * @param Token $token
     */
    public function signRequest(AbstractSignatureMethod $signatureMethod, Consumer $consumer, Token $token)
    {
        $this->parameters['oauth_signature_method'] = $signatureMethod->getName();
        $this->parameters['oauth_signature'] = $signatureMethod->buildSignature(
            $this,
            $consumer,
            $token
        );

        $this->headers['Authorization'] = $this->authorizationHeader();
    }

    public function authorizationHeader()
    {
        $parameters = http_build_query(
            $this->parameters,
            '',
            ', ',
            PHP_QUERY_RFC3986
        );

        return "OAuth $parameters";
    }
}
