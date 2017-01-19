<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Providers;

use SocialConnect\Auth\Exception\InvalidAccessToken;
use SocialConnect\Auth\Provider\Consumer;
use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use SocialConnect\Common\Http\Client\ClientInterface;
use Test\TestCase;

class VkTest extends TestCase
{
    /**
     * @param ClientInterface|null $httpClient
     * @return \SocialConnect\Vk\Provider
     */
    protected function getProvider(ClientInterface $httpClient = null)
    {
        $service = new \SocialConnect\Auth\Service([]);

        if ($httpClient) {
            $service->setHttpClient($httpClient);
        }

        return new \SocialConnect\Vk\Provider(
            $service,
            new Consumer(
                'unknown',
                'unkwown'
            )
        );
    }

    public function testParseTokenSuccess()
    {
        $expectedToken = 'XXXXXXXX';

        $accessToken = $this->getProvider()->parseToken(
            json_encode(
                [
                    'access_token' => $expectedToken
                ]
            )
        );

        parent::assertInstanceOf(AccessToken::class, $accessToken);
        parent::assertSame($expectedToken, $accessToken->getToken());
    }

    public function testParseTokenNotToken()
    {
        $this->setExpectedException(InvalidAccessToken::class);

        $accessToken = $this->getProvider()->parseToken(
            json_encode([])
        );
    }

    public function testParseTokenNotValidJSON()
    {
        $this->setExpectedException(InvalidAccessToken::class);

        $accessToken = $this->getProvider()->parseToken(
            'lelelelel'
        );
    }

    public function testGetIdentitySuccess()
    {
        $mockedHttpClient = $this->getMockBuilder(\SocialConnect\Common\Http\Client\Curl::class)
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $response = new \SocialConnect\Common\Http\Response(
            200,
            json_encode(
                [
                    'response' => [
                        [
                            'id' => $expectedId = 12321312312312,
                            'first_name' => $expectedFirstname = 'Dmitry',
                            'last_name' => $expectedLastname = 'Patsura',
                        ]
                    ]
                ]
            ),
            []
        );

        $mockedHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);


        $result = $this->getProvider($mockedHttpClient)->getIdentity(
            new AccessToken('unknown')
        );

        parent::assertInstanceOf(\SocialConnect\Common\Entity\User::class, $result);
        parent::assertSame($expectedId, $result->id);
        parent::assertSame($expectedFirstname, $result->firstname);
        parent::assertSame($expectedLastname, $result->lastname);
    }
}
