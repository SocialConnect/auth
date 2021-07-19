<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenIDConnect\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\JWX\JWT;
use SocialConnect\OAuth2\AccessToken;

class AppleTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OpenIDConnect\Provider\Apple::class;
    }

    protected function getTestResponseForGetIdentity(): ResponseInterface
    {
        return $this->createResponse(
            json_encode([
                'id' => 1,
            ])
        );
    }

    public function testGetOpenIDUrl()
    {
        $this->markTestSkipped('nothing to test, because Apple->testGetOpenIDUrl will throw an exception on call');
    }

    public function testGetIdentitySuccess()
    {
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock();

        $accessToken = $this->getMockBuilder(\SocialConnect\OpenIDConnect\AccessToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $jwtToken = $this->getMockBuilder(JWT::class)
            ->disableOriginalConstructor()
            ->getMock();

        $accessToken->expects($this->once())
            ->method('getJwt')
            ->willReturn($jwtToken);

        $this->getProvider($mockedHttpClient)->getIdentity($accessToken);
    }
}
