<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Providers;

use SocialConnect\Provider\Consumer;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\Provider\Session\SessionInterface;

class VkTest extends AbstractProviderTestCase
{
    /**
     * @param ClientInterface|null $httpClient
     * @return \SocialConnect\OAuth2\Provider\Vk
     */
    protected function getProvider(ClientInterface $httpClient = null, SessionInterface $session = null)
    {
        if (!$httpClient) {
            $httpClient = $this->getMockBuilder(\SocialConnect\Common\Http\Client\Curl::class)
                ->disableOriginalConstructor()
                ->disableProxyingToOriginalMethods()
                ->getMock();
        }

        if (!$session) {
            $session = $this->getMockBuilder(\SocialConnect\Provider\Session\Session::class)
                ->disableOriginalConstructor()
                ->disableProxyingToOriginalMethods()
                ->getMock();
        }

        return new \SocialConnect\OAuth2\Provider\Vk(
            $httpClient,
            $session,
            new Consumer(
                'unknown',
                'unkwown'
            ),
            [
                'redirectUri' => 'http://localhost:8000/'
            ]
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

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidResponse
     * @expectedExceptionMessage API response with error code
     */
    public function testGetAccessTokenResponseInternalServerErrorFail()
    {
        $this->getProvider(
            $this->mockClientResponse(
                null,
                500,
                true
            )
        )->getAccessToken('XXXXXXXXXXXX');
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

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidAccessToken
     */
    public function testParseTokenNotToken()
    {
        $this->getProvider()->parseToken(
            json_encode([])
        );
    }

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidAccessToken
     */
    public function testParseTokenNotValidJSON()
    {
        $this->getProvider()->parseToken(
            'lelelelel'
        );
    }

    public function testGetIdentitySuccess()
    {
        $mockedHttpClient = $this->mockClientResponse(
            json_encode(
                [
                    'response' => [
                        [
                            'id' => $expectedId = 12321312312312,
                            'first_name' => $expectedFirstname = 'Dmitry',
                            'last_name' => $expectedLastname = 'Patsura',
                            'sex' => 1,
                        ]
                    ]
                ]
            )
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
        parent::assertSame('female', $result->sex);
    }

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidResponse
     * @expectedExceptionMessage API response with error code
     */
    public function testGetIdentityInternalServerError()
    {
        $mockedHttpClient = $this->mockClientResponse(
            [],
            500
        );

        $result = $this->getProvider($mockedHttpClient)->getIdentity(
            new AccessToken(
                [
                    'access_token' => '123456789'
                ]
            )
        );
    }

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidResponse
     * @expectedExceptionMessage API response is not a valid JSON object
     */
    public function testGetIdentityNotData()
    {
        $mockedHttpClient = $this->mockClientResponse(
            [],
            200
        );

        $result = $this->getProvider($mockedHttpClient)->getIdentity(
            new AccessToken(
                [
                    'access_token' => '123456789'
                ]
            )
        );
    }
}
