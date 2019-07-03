<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenID\Provider;

use Psr\Http\Client\ClientInterface;
use ReflectionClass;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Session\SessionInterface;
use Test\TestCase;

abstract class AbstractProviderTestCase extends TestCase
{
    /**
     * @return string
     */
    abstract protected function getProviderClassName();

    /**
     * @param array|null $responseData
     * @param int $responseCode
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockClientResponse($responseData, int $responseCode = 200)
    {
        $mockedHttpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock();

        $response = new \SocialConnect\Common\Http\Response(
            $responseCode,
            $responseData,
            []
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
     * @return \SocialConnect\OpenID\AbstractProvider
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
            $session = $this->getMockBuilder(\SocialConnect\Provider\Session\Session::class)
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

    public function testGetOpenIDUrl()
    {
        parent::assertInternalType('string', $this->getProvider()->getOpenIdUrl());
    }

    public function testGetBaseUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getBaseUri());
    }

    public function testGetNameReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getName());
    }
}
