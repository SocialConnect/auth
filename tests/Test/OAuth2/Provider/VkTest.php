<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use SocialConnect\Provider\Consumer;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\Provider\Session\SessionInterface;

class VkTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\Vk::class;
    }

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
