<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\OAuth2\AbstractProvider;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\OAuth2\Exception\Unauthorized;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Provider\Session\SessionInterface;

abstract class AbstractProviderTestCase extends \Test\Provider\AbstractProviderTestCase
{
    /**
     * @param ClientInterface|null $httpClient
     * @param SessionInterface|null $session
     * @return AbstractProvider
     */
    protected function getProvider(?ClientInterface $httpClient = null, ?SessionInterface $session = null)
    {
        $provider = parent::getProvider($httpClient, $session);

        if (!$provider instanceof AbstractProvider) {
            throw new \RuntimeException('Test is trying to get instance of non OAuth2 provider');
        }

        return $provider;
    }

    public function testGetBaseUriReturnString()
    {
        parent::assertIsString($this->getProvider()->getBaseUri());
    }

    public function testGetAuthorizeUriReturnString()
    {
        parent::assertIsString($this->getProvider()->getAuthorizeUri());
    }

    public function testGetRequestTokenUriReturnString()
    {
        parent::assertIsString($this->getProvider()->getRequestTokenUri());
    }

    public function testGetNameReturnString()
    {
        parent::assertIsString($this->getProvider()->getName());
    }

    public function testMakeAuthUrl()
    {
        $provider = $this->getProvider();

        $authUrl = $provider->makeAuthUrl();
        parent::assertIsString($authUrl);

        /**
         * Auth url must be started from getAuthorizeUri
         */
        parent::assertSame(
            0,
            strpos($authUrl, $provider->getAuthorizeUri())
        );

        $query = parse_url($authUrl, PHP_URL_QUERY);
        parent::assertIsString($query);

        parse_str($query, $queryParameters);
        parent::assertIsArray($queryParameters);

        parent::assertArrayHasKey('client_id', $queryParameters);
        parent::assertArrayHasKey('redirect_uri', $queryParameters);
        parent::assertArrayHasKey('response_type', $queryParameters);
        parent::assertArrayHasKey('state', $queryParameters);
    }

    public function testGetAccessTokenResponseInternalServerErrorFail()
    {
        $this->expectException(InvalidResponse::class);
        $this->expectExceptionMessage('API response with error code');

        $client = $this->mockClientResponse(
            null,
            500
        );
        $this->getProvider($client)->getAccessToken('XXXXXXXXXXXX');
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
            new AccessToken(
                [
                    'access_token' => '123456789'
                ]
            )
        );
    }

    public function testGetIdentityInternalServerError()
    {
        $this->expectException(InvalidResponse::class);
        $this->expectExceptionMessage('API response with error code');

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

    public function testGetIdentityNotValidJSON()
    {
        $this->expectException(InvalidResponse::class);
        $this->expectExceptionMessage('API response is not a valid JSON object');

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

    public function testParseTokenEmptyBody()
    {
        $this->expectException(InvalidAccessToken::class);
        $this->expectExceptionMessage('Provider response with empty body');

        $this->getProvider()->parseToken(
            ''
        );
    }

    public function testParseTokenNotToken()
    {
        $this->expectException(InvalidAccessToken::class);

        $this->getProvider()->parseToken(
            json_encode([])
        );
    }

    public function testParseTokenNotValidJSON()
    {
        $this->expectException(InvalidAccessToken::class);

        $this->getProvider()->parseToken(
            'lelelelel'
        );
    }

    public function testParseTokenSuccess()
    {
        $expectedToken = 'XXXXXXXX';
        $expectedUserId = '123456';

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

    public function testAccessDenied()
    {
        $this->expectException(Unauthorized::class);

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

        parent::assertIsString($state);
        parent::assertEquals(32, mb_strlen($state));
    }
}
