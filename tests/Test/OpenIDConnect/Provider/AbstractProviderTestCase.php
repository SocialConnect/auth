<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenIDConnect\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\OpenIDConnect\AbstractProvider;
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
            throw new \RuntimeException('Test is trying to get instance of non OpenIDConnect provider');
        }

        return $provider;
    }

    public function testGetAuthorizeUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getAuthorizeUri());
    }

    public function testGetRequestTokenUri()
    {
        parent::assertInternalType('string', $this->getProvider()->getRequestTokenUri());
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

    /**
     * @expectedExceptionMessage Provider response with empty body
     * @expectedException \SocialConnect\Provider\Exception\InvalidAccessToken
     */
    public function testParseTokenEmptyBody()
    {
        $this->getProvider()->parseToken(
            ''
        );
    }

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidAccessToken
     */
    public function testParseTokenNotToken()
    {
        $this->getProvider()->parseToken(
            json_encode([])
        );
    }
}
