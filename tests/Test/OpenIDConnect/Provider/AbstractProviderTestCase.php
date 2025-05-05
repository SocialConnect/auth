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
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Session\SessionInterface;

abstract class AbstractProviderTestCase extends \Test\Provider\AbstractProviderTestCase
{
    /**
     * @param ClientInterface|null $httpClient
     * @param SessionInterface|null $session
     * @return AbstractProvider
     */
    protected function getProvider(?ClientInterface $httpClient = null, ?SessionInterface $session = null)
    {
        $provider = parent::getProvider($httpClient, $session);

        if (!$provider instanceof AbstractProvider) {
            throw new \RuntimeException('Test is trying to get instance of non OpenIDConnect provider');
        }

        return $provider;
    }

    public function testGetAuthorizeUriReturnString()
    {
        parent::assertIsString($this->getProvider()->getAuthorizeUri());
    }

    public function testGetRequestTokenUri()
    {
        parent::assertIsString($this->getProvider()->getRequestTokenUri());
    }

    public function testGetOpenIDUrl()
    {
        parent::assertIsString($this->getProvider()->getOpenIdUrl());
    }

    public function testGetBaseUriReturnString()
    {
        parent::assertIsString($this->getProvider()->getBaseUri());
    }

    public function testGetNameReturnString()
    {
        parent::assertIsString($this->getProvider()->getName());
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

    public function testParseTokenEmptyBody()
    {
        $this->expectException(InvalidAccessToken::class);
        $this->expectExceptionMessage('Provider response with empty body');

        $this->getProvider()->parseToken(
            ''
        );
    }

    public function testParseTokenNotToken()
    {
        $this->expectException(InvalidAccessToken::class);

        $this->getProvider()->parseToken(
            json_encode([])
        );
    }
}
