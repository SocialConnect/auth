<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth1\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\OAuth1\AccessToken;
use SocialConnect\OAuth1\AbstractProvider;
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
            throw new \RuntimeException('Test is trying to get instance of non OAuth1 provider');
        }

        return $provider;
    }

    public function testGetBaseUriReturnString()
    {
        parent::assertIsString($this->getProvider()->getBaseUri());
    }

    public function testGetRequestTokenAccessUriReturnString()
    {
        parent::assertIsString($this->getProvider()->getRequestTokenAccessUri());
    }

    public function testGetAuthorizeUriReturnString()
    {
        parent::assertIsString($this->getProvider()->getAuthorizeUri());
    }

    public function testGetRequestTokenUriReturnString()
    {
        parent::assertIsString($this->getProvider()->getRequestTokenUri());
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
            new AccessToken([
                'oauth_token' => 'key',
                'oauth_token_secret' => 'secret',
            ])
        );
    }
}
