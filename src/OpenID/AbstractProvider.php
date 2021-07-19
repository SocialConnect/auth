<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OpenID;

use SocialConnect\Common\Exception\Unsupported;
use SocialConnect\OpenID\Exception\Unauthorized;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;

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
        $params = [
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => $immediate ? 'checkid_immediate' : 'checkid_setup',
            'openid.return_to' => $this->getRedirectUrl(),
            'openid.realm' => $this->getRedirectUrl()
        ];

        $params['openid.ns.sreg'] = 'http://openid.net/extensions/sreg/1.1';
        $params['openid.identity'] = $params['openid.claimed_id'] = 'http://specs.openid.net/auth/2.0/identifier_select';

        return $this->loginEntrypoint . '?' . http_build_query($params, '', '&');
    }

    /**
     * @param string $url
     * @return string
     * @throws InvalidResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function discover(string $url)
    {
        $response = $this->executeRequest(
            $this->httpStack->createRequest('GET', $url)
        );

        $contentType = $response->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/xrds+xml;charset=utf-8') === false) {
            throw new InvalidResponse(
                'Unexpected Content-Type',
                $response
            );
        }

        $xml = new \SimpleXMLElement($response->getBody()->getContents());

        $this->version = 2;
        $this->loginEntrypoint = (string)$xml->XRD->Service->URI;

        return $this->getOpenIdUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function makeAuthUrl(): string
    {
        $this->discover($this->getOpenIdUrl());

        return $this->makeAuthUrlV2(false);
    }

    /**
     * @param string $identity
     * @return string
     */
    abstract protected function parseUserIdFromIdentity($identity): string;

    protected function getRequiredRequestParameter(array $requestParameters, string $key)
    {
        if (isset($requestParameters[$key])) {
            return $requestParameters[$key];
        }

        throw new Unauthorized("There is no required parameter called: '${key}'");
    }

    /**
     * @param string $nonce
     * @return int
     * @throws Unauthorized
     */
    protected function splitNonce(string $nonce): int
    {
        $result = preg_match('/(\d{4})-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)Z(.*)/', $nonce, $matches);
        if ($result !== 1 || count($matches) !== 8) {
            throw new Unauthorized('Unexpected nonce format');
        }

        list(,$year, $month, $day, $hour, $min, $sec) = $matches;

        try {
            $timestamp = new \DateTime();
            $timestamp->setDate((int) $year, (int) $month, (int) $day);
            $timestamp->setTime((int) $hour, (int) $min, (int) $sec);
        } catch (\Throwable $e) {
            throw new Unauthorized('Timestamp from nonce is not valid', $e->getCode(), $e);
        }

        return $timestamp->getTimestamp();
    }

    /**
     * @param string $nonce
     * @return void
     * @throws Unauthorized
     */
    protected function checkNonce(string $nonce): void
    {
        $stamp = $this->splitNonce($nonce);

        $skew = 60;
        $now = time();

        if ($stamp <= $now - $skew) {
            throw new Unauthorized("Timestamp from nonce is earlier then time() - {$skew}s");
        }

        if ($stamp >= $now + $skew) {
            throw new Unauthorized("Timestamp from nonce is older then time() + {$skew}s");
        }
    }

    /**
     * @link http://openid.net/specs/openid-authentication-2_0.html#verification
     *
     * @param array $requestParameters
     * @return AccessToken
     * @throws InvalidAccessToken
     * @throws InvalidResponse
     * @throws Unauthorized
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAccessTokenByRequestParameters(array $requestParameters)
    {
        $nonce = $this->getRequiredRequestParameter($requestParameters, 'openid_response_nonce');
        $this->checkNonce($nonce);

        $params = [
            'openid.assoc_handle' => $this->getRequiredRequestParameter($requestParameters, 'openid_assoc_handle'),
            'openid.signed' => $this->getRequiredRequestParameter($requestParameters, 'openid_signed'),
            'openid.sig' => $this->getRequiredRequestParameter($requestParameters, 'openid_sig'),
            'openid.ns' => $this->getRequiredRequestParameter($requestParameters, 'openid_ns'),
            'openid.op_endpoint' => $requestParameters['openid_op_endpoint'],
            'openid.claimed_id' => $requestParameters['openid_claimed_id'],
            'openid.identity' => $requestParameters['openid_identity'],
            'openid.return_to' => $this->getRedirectUrl(),
            'openid.response_nonce' => $nonce,
            'openid.mode' => 'check_authentication'
        ];

        if (isset($requestParameters['openid_claimed_id'])) {
            $claimedId = $requestParameters['openid_claimed_id'];
        } else {
            $claimedId = $requestParameters['openid_identity'];
        }

        $this->discover($claimedId);

        $response = $this->executeRequest(
            $this->httpStack->createRequest('POST', $this->loginEntrypoint)
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
                ->withBody($this->httpStack->createStream(http_build_query($params, '', '&')))
        );

        $content = $response->getBody()->getContents();
        if (preg_match('/is_valid\s*:\s*true/i', $content)) {
            return new AccessToken(
                $requestParameters['openid_identity'],
                $this->parseUserIdFromIdentity(
                    $requestParameters['openid_identity']
                )
            );
        }

        throw new InvalidAccessToken;
    }

    /**
     * {@inheritDoc}
     */
    protected function createConsumer(array $parameters): Consumer
    {
        return new Consumer(
            $this->getRequiredStringParameter('applicationId', $parameters),
            ''
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createAccessToken(array $information)
    {
        throw new Unsupported('It\'s usefull to use this method for OpenID, are you sure?');
    }
}
