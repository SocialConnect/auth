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
        $service = new \SocialConnect\Auth\Service(
            [
                'redirectUri' => 'http://localhost:8000/'
            ]
        );

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

    public function testMakeAuthUrl()
    {
        parent::assertSame(
            'https://oauth.vk.com/authorize?client_id=unknown&redirect_uri=http%3A%2F%2Flocalhost%3A8000%2F%2Fvk%2F&response_type=code',
            $this->getProvider()->makeAuthUrl()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter $code must be a string
     */
    public function testGetAccessTokenFail()
    {
        $this->getProvider()->getAccessToken(null);
    }


    public function testMakeAccessTokenRequest()
    {
        $expectedCode = 'djsflkSdjflskdfjFlsd9';

        $provider = $this->getProvider();

        /** @var \SocialConnect\Common\Http\Request $request */
        $request = parent::callProtectedMethod(
            $provider,
            'makeAccessTokenRequest',
            [
                $expectedCode
            ]
        );

        parent::assertInstanceOf(\SocialConnect\Common\Http\Request::class, $request);
        parent::assertSame($provider->getRequestTokenUri(), $request->getUri());
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
