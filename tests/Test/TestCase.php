<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use ReflectionClass;
use SocialConnect\Auth\Service;
use SocialConnect\HttpClient\RequestFactory;
use SocialConnect\HttpClient\Response;
use SocialConnect\HttpClient\StreamFactory;
use SocialConnect\Provider\HttpStack;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string|null $responseData
     * @param int $responseCode
     * @return ResponseInterface
     */
    protected function createResponse($responseData, int $responseCode = 200): ResponseInterface
    {
        $body = null;

        if ($responseData) {
            $body = $this->getStreamFactoryMock()->createStream(
                $responseData
            );
        }

        return new Response(
            $responseCode,
            [],
            $body
        );
    }

    /**
     * @param string|null $responseData
     * @param int $responseCode
     * @return \PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    protected function mockClientResponse($responseData, int $responseCode = 200)
    {
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock();

        $mockedHttpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($this->createResponse($responseData, $responseCode));

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

    protected function getRequestFactoryMock(): RequestFactoryInterface
    {
        return new RequestFactory();
    }

    protected function getStreamFactoryMock(): StreamFactoryInterface
    {
        return new StreamFactory();
    }

    protected function getHttpStackMock(ClientInterface $httpClient = null)
    {
        if (!$httpClient) {
            $httpClient = $this->getMockBuilder(ClientInterface::class)
                ->disableOriginalConstructor()
                ->disableProxyingToOriginalMethods()
                ->getMock();
        }

        return new HttpStack(
            $httpClient,
            $this->getRequestFactoryMock(),
            $this->getStreamFactoryMock()
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
                    'vk' => array(
                        'applicationId' => '123456',
                        'applicationSecret' => 'Secret'
                    )
                )
            ),
            null
        );

        return $service;
    }
}
