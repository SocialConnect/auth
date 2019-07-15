<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenID\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\OpenID\AbstractProvider;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Provider\Session\SessionInterface;

abstract class AbstractProviderTestCase extends \Test\Provider\AbstractProviderTestCase
{
    /**
     * @param ClientInterface|null $httpClient
     * @param SessionInterface|null $session
     * @return AbstractProvider
     */
    protected function getProvider(ClientInterface $httpClient = null, SessionInterface $session = null)
    {
        $provider = parent::getProvider($httpClient, $session);

        if (!$provider instanceof AbstractProvider) {
            throw new \RuntimeException('Test is trying to get instance of non OpenID provider');
        }

        return $provider;
    }

    public function testGetOpenIDUrl()
    {
        parent::assertInternalType('string', $this->getProvider()->getOpenIdUrl());
    }

    public function testGetBaseUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getBaseUri());
    }

    public function testGetNameReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getName());
    }

    /**
     * @return ResponseInterface
     */
    abstract protected function getTestResponseForGetIdentity(): ResponseInterface;

    public function testGetIdentitySuccess()
    {
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock();

        $mockedHttpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($this->getTestResponseForGetIdentity());

        $this->getProvider($mockedHttpClient)->getIdentity(
            new AccessToken(
                [
                    'access_token' => '123456789'
                ]
            )
        );
    }

    public function testMakeAuthUrlWithVersionTwoSpec()
    {
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock();

        $mockedHttpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(
                $this->createResponse(
                    '<?xml version="1.0" encoding="UTF-8"?>
<xrds:XRDS xmlns:xrds="xri://$xrds" xmlns="xri://$xrd*($v*2.0)">
	<XRD>
		<Service priority="0">
			<Type>http://specs.openid.net/auth/2.0/signon</Type>
			<URI>https://steamcommunity.com/openid/login</URI>
		</Service>
	</XRD>
</xrds:XRDS>',
                    200,
                    [
                        'Content-Type' => 'application/xrds+xml;charset=utf-8'
                    ]
                )
            );

        parent::assertSame(
            'https://steamcommunity.com/openid/login?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.mode=checkid_setup&openid.return_to=http%3A%2F%2Flocalhost%3A8000%2Fsteam%2F&openid.realm=http%3A%2F%2Flocalhost%3A8000%2Fsteam%2F&openid.ns.sreg=http%3A%2F%2Fopenid.net%2Fextensions%2Fsreg%2F1.1&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select',
            $this->getProvider($mockedHttpClient)->makeAuthUrl()
        );
    }

    public function testGetAccessTokenByRequestParametersSuccess()
    {
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock();

        $mockedHttpClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturn(
                $this->createResponse(
                    '<?xml version="1.0" encoding="UTF-8"?>
<xrds:XRDS xmlns:xrds="xri://$xrds" xmlns="xri://$xrd*($v*2.0)">
	<XRD>
		<Service priority="0">
			<Type>http://specs.openid.net/auth/2.0/signon</Type>
			<URI>https://steamcommunity.com/openid/login</URI>
		</Service>
	</XRD>
</xrds:XRDS>',
                    200,
                    [
                        'Content-Type' => 'application/xrds+xml;charset=utf-8'
                    ]
                ),
                $this->createResponse(
                    "ns:http://specs.openid.net/auth/2.0\nis_valid:true",
                    200
                )
            )
        ;

        $oauthToken = $this->getProvider($mockedHttpClient)->getAccessTokenByRequestParameters([
            'openid_ns' => 'http://specs.openid.net/auth/2.0',
            'openid_op_endpoint' => 'https://steamcommunity.com/openid/login',
            'openid_assoc_handle' => '1234567890',
            'openid_signed' => 'signed,op_endpoint,claimed_id,identity,return_to,response_nonce,assoc_handle',
            'openid_claimed_id' => 'https://steamcommunity.com/openid/id/76561198066894048',
            'openid_identity' => 'https://steamcommunity.com/openid/id/76561198066894048',
            'openid_response_nonce' => 'nonce',
            'openid_sig' => 'test',
        ]);

        parent::assertSame(
            '76561198066894048',
            $oauthToken->getUserId()
        );
    }
}
