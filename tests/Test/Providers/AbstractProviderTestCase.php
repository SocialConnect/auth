<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Providers;

use ReflectionClass;
use SocialConnect\Auth\Provider\Exception\InvalidAccessToken;
use SocialConnect\Auth\Consumer;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Http\Client\ClientInterface;
use Test\TestCase;

class AbstractProviderTestCase extends TestCase
{
    /**
     * @param mixed $responseData
     * @param int $responseCode
     * @param bool $mockFromRequest
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockClientResponse($responseData, $responseCode = 200, $mockFromRequest = false)
    {
        $mockedHttpClient = $this->getMockBuilder(\SocialConnect\Common\Http\Client\Curl::class)
            ->getMock();

        $response = new \SocialConnect\Common\Http\Response(
            $responseCode,
            $responseData,
            []
        );

        if ($mockFromRequest) {
            $mockedHttpClient->expects($this->once())
                ->method('fromRequest')
                ->willReturn($response);
        } else {
            $mockedHttpClient->expects($this->once())
                ->method('request')
                ->willReturn($response);
        }

        return $mockedHttpClient;
    }

    /**
     * @param object $object
     * @param string $name
     * @param array $params
     * @return mixed
     */
    protected static function callProtectedMethod($object, $name, array $params = [])
    {
        $class = new ReflectionClass($object);

        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $params);
    }
}
