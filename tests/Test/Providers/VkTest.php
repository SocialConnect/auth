<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Providers;

use SocialConnect\Auth\Provider\Exception\InvalidAccessToken;
use SocialConnect\Auth\Consumer;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Http\Client\ClientInterface;
use Test\TestCase;

class VkTest extends AbstractProviderTestCase
{
    /**
     * @param ClientInterface|null $httpClient
     * @return \SocialConnect\Auth\Provider\Vk
     */
    protected function getProvider(ClientInterface $httpClient = null)
    {
        $service = new \SocialConnect\Auth\Service([]);

        if ($httpClient) {
            $service->setHttpClient($httpClient);
        }

        return new \SocialConnect\Auth\Provider\Vk(
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
        $expectedUserId = 123456;

        $accessToken = $this->getProvider()->parseToken(
            json_encode(
                [
                    'access_token' => $expectedToken,
                    'user_id' => $expectedUserId
                ]
            )
        );

        parent::assertInstanceOf(AccessToken::class, $accessToken);
        parent::assertSame($expectedToken, $accessToken->getToken());
        parent::assertSame($expectedUserId, $accessToken->getUserId());
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
        $mockedHttpClient = $this->makeIdentityClientResponse(
            [
                'response' => [
                    [
                        'id' => $expectedId = 12321312312312,
                        'first_name' => $expectedFirstname = 'Dmitry',
                        'last_name' => $expectedLastname = 'Patsura',
                    ]
                ]
            ]
        );

        $result = $this->getProvider($mockedHttpClient)->getIdentity(
            new AccessToken(
                [
                    'access_token' => '123456789'
                ]
            )
        );

        parent::assertInstanceOf(\SocialConnect\Common\Entity\User::class, $result);
        parent::assertSame($expectedId, $result->id);
        parent::assertSame($expectedFirstname, $result->firstname);
        parent::assertSame($expectedLastname, $result->lastname);
    }
}
