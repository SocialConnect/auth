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
use SocialConnect\Common\HttpStack;
use SocialConnect\Provider\Session\SessionInterface;

abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    protected function getClassProperty($property, $object)
    {
        $reflection = new ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @param string|null $responseData
     * @param int $responseCode
     * @param array $headers
     * @return ResponseInterface
     */
    protected function createResponse($responseData, int $responseCode = 200, array $headers = []): ResponseInterface
    {
        $body = null;

        if ($responseData) {
            $body = $this->getStreamFactoryMock()->createStream(
                $responseData
            );
        }

        return new Response(
            $responseCode,
            $headers,
            $body
        );
    }

    /**
     * @param string|null $responseData
     * @param int $responseCode
     * @return \PHPUnit\Framework\MockObject\MockObject|ResponseInterface
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
     * @param array $values
     * @return SessionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockSession(array $values)
    {
        $mockedSession = $this->getMockBuilder(SessionInterface::class)
            ->getMock();

        $mockedSession->expects($this->exactly(count($values)))
            ->method('get')
            ->willReturn(...$values);

        return $mockedSession;
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

    protected function getHttpStackMock(?ClientInterface $httpClient = null)
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
