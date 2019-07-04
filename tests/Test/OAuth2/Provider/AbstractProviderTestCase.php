<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use Psr\Http\Client\ClientInterface;
use SocialConnect\Common\Http\Response;
use SocialConnect\OAuth2\AccessToken;
use function GuzzleHttp\Psr7\stream_for;

abstract class AbstractProviderTestCase extends \Test\Provider\AbstractProviderTestCase
{
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
     * @return Response
     */
    abstract protected function getTestResponseForGetIdentity(): Response;

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
     * @throws \SocialConnect\Provider\Exception\InvalidAccessToken
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
