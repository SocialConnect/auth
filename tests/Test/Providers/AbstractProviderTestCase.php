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

class AbstractProviderTestCase extends TestCase
{
    protected function makeIdentityClientResponse(array $responseData)
    {
        $mockedHttpClient = $this->getMockBuilder(\SocialConnect\Common\Http\Client\Curl::class)
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $response = new \SocialConnect\Common\Http\Response(
            200,
            json_encode(
                $responseData
            ),
            []
        );

        $mockedHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        return $mockedHttpClient;
    }
}
