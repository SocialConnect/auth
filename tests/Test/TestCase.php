<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use SocialConnect\Auth\Service;
use SocialConnect\Common\Http\HttpStack;
use SocialConnect\Common\Http\RequestFactory;
use SocialConnect\Common\Http\Response;
use SocialConnect\Common\Http\StreamFactory;
use function GuzzleHttp\Psr7\stream_for;

class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @param string|null $responseData
     * @param int $responseCode
     * @return \PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    protected function mockClientResponse($responseData, $responseCode = 200)
    {
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock();

        $response = new Response(
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

    protected function getHttpStackMock()
    {
        $httpClient = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        return new HttpStack(
            $httpClient,
            new RequestFactory(),
            new StreamFactory()
        );
    }

    protected function getService()
    {
        $session = $this->getMockBuilder(\SocialConnect\Provider\Session\Session::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $service = new Service(
            $this->getHttpStackMock(),
            $session,
            array(
                'provider' => array(
                    'Vk' => array(
                        'applicationId' => 123456,
                        'applicationSecret' => 'Secret'
                    )
                )
            ),
            null
        );

        return $service;
    }
}
