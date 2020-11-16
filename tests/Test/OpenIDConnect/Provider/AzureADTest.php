<?php

namespace Test\OpenIDConnect\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\JWX\JWT;
use SocialConnect\OAuth2\AccessToken;

class AzureADTest extends AbstractProviderTestCase
{
    public function getProviderConfiguration(): array
    {
        $configuration = parent::getProviderConfiguration();
        $configuration['directoryId'] = 'directoryId';

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OpenIDConnect\Provider\AzureAD::class;
    }

    protected function getTestResponseForGetIdentity(): ResponseInterface
    {
        return $this->createResponse(
            json_encode([
                'id' => 1,
            ])
        );
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
