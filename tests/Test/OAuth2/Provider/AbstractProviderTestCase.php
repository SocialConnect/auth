<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use SocialConnect\OAuth2\AbstractProvider;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Session\SessionInterface;
use Test\TestCase;
use function GuzzleHttp\Psr7\stream_for;

abstract class AbstractProviderTestCase extends TestCase
{
    /**
     * @return string
     */
    abstract protected function getProviderClassName();

    /**
     * @param string|null $responseData
     * @param int $responseCode
     * @return \PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    protected function mockClientResponse($responseData, $responseCode = 200)
    {
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock();

        $response = new \GuzzleHttp\Psr7\Response(
            $responseCode,
            [],
            stream_for($responseData)
        );

        $mockedHttpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        return $mockedHttpClient;
    }

    /**
     * @param object $object
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws \ReflectionException
     */
    protected static function callProtectedMethod($object, $name, array $params = [])
    {
        $class = new ReflectionClass($object);

        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $params);
    }

    /**
     * @param ClientInterface|null $httpClient
     * @param SessionInterface|null $session
     * @return AbstractProvider
     */
    protected function getProvider(ClientInterface $httpClient = null, SessionInterface $session = null)
    {
        if (!$httpClient) {
            $httpClient = $this->getMockBuilder(ClientInterface::class)
                ->disableOriginalConstructor()
                ->disableProxyingToOriginalMethods()
                ->getMock();
        }

        if (!$session) {
            $session = $this->getMockBuilder(SessionInterface::class)
                ->disableOriginalConstructor()
                ->disableProxyingToOriginalMethods()
                ->getMock();
        }

        $className = $this->getProviderClassName();

        return new $className(
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

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage Argument 1 passed to SocialConnect\OAuth2\AbstractProvider::getAccessToken() must be of the type string, null given
     */
    public function testGetAccessTokenFail()
    {
        $this->getProvider()->getAccessToken(null);
    }

    public function testGetBaseUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getBaseUri());
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

    public function testMakeAuthUrl()
    {
        $provider = $this->getProvider();

        $authUrl = $provider->makeAuthUrl();
        parent::assertInternalType('string', $authUrl);

        /**
         * Auth url must be started from getAuthorizeUri
         */
        parent::assertSame(
            0,
            strpos($authUrl, $provider->getAuthorizeUri())
        );

        $query = parse_url($authUrl, PHP_URL_QUERY);
        parent::assertInternalType('string', $query);

        parse_str($query, $queryParameters);
        parent::assertInternalType('array', $queryParameters);

        parent::assertArrayHasKey('client_id', $queryParameters);
        parent::assertArrayHasKey('redirect_uri', $queryParameters);
        parent::assertArrayHasKey('response_type', $queryParameters);
        parent::assertArrayHasKey('state', $queryParameters);
    }

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidResponse
     * @expectedExceptionMessage API response with error code
     */
    public function testGetAccessTokenResponseInternalServerErrorFail()
    {
        $client = $this->mockClientResponse(
            null,
            500,
            true
        );
        $this->getProvider($client)->getAccessToken('XXXXXXXXXXXX');
    }

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidResponse
     * @expectedExceptionMessage API response with error code
     */
    public function testGetIdentityInternalServerError()
    {
        $mockedHttpClient = $this->mockClientResponse(
            null,
            500
        );

        $this->getProvider($mockedHttpClient)->getIdentity(
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
    public function testGetIdentityNotValidJSON()
    {
        $mockedHttpClient = $this->mockClientResponse(
            'NOT VALID JSON',
            200
        );

        $this->getProvider($mockedHttpClient)->getIdentity(
            new AccessToken(
                [
                    'access_token' => '123456789'
                ]
            )
        );
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
     * @expectedException \SocialConnect\OAuth2\Exception\Unauthorized
     */
    public function testAccessDenied()
    {
        $sessionMock = $this->getMockBuilder(\SocialConnect\Provider\Session\Session::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $provider = $this->getProvider(null, $sessionMock);

        $provider->getAccessTokenByRequestParameters(['error' => 'access_denied']);
    }

    public function testGenerateState()
    {
        $state = $this->callProtectedMethod($this->getProvider(), 'generateState', []);

        parent::assertInternalType('string', $state);
        parent::assertEquals(32, mb_strlen($state));
    }
}
