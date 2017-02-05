<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider\OpenID;

use SocialConnect\Auth\Exception\InvalidAccessToken;
use SocialConnect\Auth\Exception\InvalidResponse;
use SocialConnect\Auth\Provider\AbstractBaseProvider;
use SocialConnect\Common\Http\Client\Client;

abstract class AbstractProvider extends AbstractBaseProvider
{
    /**
     * @return string
     */
    abstract public function getOpenIdUrl();

    /**
     * @var int
     */
    protected $version;

    /**
     * @var string
     */
    protected $loginEntrypoint;

    /**
     * @param bool $immediate
     * @return string
     */
    protected function makeAuthUrlV2($immediate)
    {
        $params = array(
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => $immediate ? 'checkid_immediate' : 'checkid_setup',
            'openid.return_to' => $this->getRedirectUrl(),
            'openid.realm' => $this->getRedirectUrl()
        );

        $params['openid.ns.sreg'] = 'http://openid.net/extensions/sreg/1.1';
        $params['openid.identity'] = $params['openid.claimed_id'] = 'http://specs.openid.net/auth/2.0/identifier_select';

        return $this->loginEntrypoint . '?' . http_build_query($params, '', '&');
    }

    /**
     * @param string $url
     * @return string
     * @throws \SocialConnect\Auth\Exception\InvalidResponse
     */
    protected function discover($url)
    {
        $response = $this->service->getHttpClient()->request(
            $url,
            [],
            Client::GET,
            [
                'Content-Type' => 'application/json'
            ]
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        if (!$response->hasHeader('Content-Type')) {
            throw new InvalidResponse(
                'Unknown Content-Type',
                $response
            );
        }

        $contentType = $response->getHeader('Content-Type');
        if (strpos($contentType, 'application/xrds+xml;charset=utf-8') === false) {
            throw new InvalidResponse(
                'Unexpected Content-Type',
                $response
            );
        }

        $xml = new \SimpleXMLElement($response->getBody());

        $this->version = 2;
        $this->loginEntrypoint = $xml->XRD->Service->URI;

        return $this->getOpenIdUrl();
    }

    public function makeAuthUrl()
    {
        $this->discover($this->getOpenIdUrl());

        return $this->makeAuthUrlV2(false);
    }

    /**
     * @link http://openid.net/specs/openid-authentication-2_0.html#verification
     *
     * @param $requestParameters
     * @return AccessToken
     * @throws \SocialConnect\Auth\Exception\InvalidAccessToken
     */
    public function getAccessTokenByRequestParameters($requestParameters)
    {
        $params = array(
            'openid.assoc_handle' => $requestParameters['openid_assoc_handle'],
            'openid.signed' => $requestParameters['openid_signed'],
            'openid.sig' => $requestParameters['openid_sig'],
            'openid.ns' => $requestParameters['openid_ns'],
            'openid.op_endpoint' => $requestParameters['openid_op_endpoint'],
            'openid.claimed_id' => $requestParameters['openid_claimed_id'],
            'openid.identity' => $requestParameters['openid_identity'],
            'openid.return_to' => $this->getRedirectUrl(),
            'openid.response_nonce' => $requestParameters['openid_response_nonce'],
            'openid.mode' => 'check_authentication'
        );

        if (isset($requestParameters['openid_claimed_id'])) {
            $claimedId = $requestParameters['openid_claimed_id'];
        } else {
            $claimedId = $requestParameters['openid_identity'];
        }

        $this->discover($claimedId);

        $response = $this->service->getHttpClient()->request(
            $this->loginEntrypoint,
            $params,
            Client::POST
        );

        if (preg_match('/is_valid\s*:\s*true/i', $response->getBody())) {
            return new AccessToken($requestParameters['openid_identity']);
        }

        throw new InvalidAccessToken;
    }
}
