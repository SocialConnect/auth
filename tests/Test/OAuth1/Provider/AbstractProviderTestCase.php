<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth1\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\OAuth1\AccessToken;

abstract class AbstractProviderTestCase extends \Test\Provider\AbstractProviderTestCase
{
    public function testGetBaseUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getBaseUri());
    }

    public function testGetRequestTokenAccessUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getRequestTokenAccessUri());
    }

    public function testGetAuthorizeUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getAuthorizeUri());
    }

    public function testGetRequestTokenUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getRequestTokenUri());
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
            new AccessToken('key', 'secret')
        );
    }
}
