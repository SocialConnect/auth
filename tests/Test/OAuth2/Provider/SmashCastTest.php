<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\OAuth2\AccessToken;

class SmashCastTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\SmashCast::class;
    }

    public function testMakeAuthUrl()
    {
        parent::markTestSkipped('Please finish');
    }

    public function testGetIdentitySuccess()
    {
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock();

        $mockedHttpClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturn(
                new Response(
                    200,
                    [],
                    json_encode([
                        'user_name' => 'ovr',
                    ])
                ),
                $this->getTestResponseForGetIdentity()
            )
        ;

        $this->getProvider($mockedHttpClient)->getIdentity(
            new AccessToken(
                [
                    'access_token' => '123456789'
                ]
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestResponseForGetIdentity(): ResponseInterface
    {
        return $this->createResponse(
            json_encode([
                'id' => 12345,
            ])
        );
    }
}
