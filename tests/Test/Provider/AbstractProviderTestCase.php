<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Provider;

use Psr\Http\Client\ClientInterface;
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
     * This data is used inside getProvider
     * @return Consumer
     */
    public function getProviderConsumer(): Consumer
    {
        return new Consumer(
            'unknown',
            'unkwown'
        );
    }

    /**
     * This data is used inside getProvider
     * @return array
     */
    public function getProviderConfiguration(): array
    {
        return [
            'redirectUri' => 'http://localhost:8000/',
            'scope' => [
                'user',
                'email'
            ]
        ];
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
            $this->getProviderConsumer(),
            $this->getProviderConfiguration()
        );
    }

    /**
     * @param ClientInterface $httpClient
     * @param SessionInterface|null $session
     * @return ProviderMock
     */
    protected function getAbstractProviderMock(ClientInterface $httpClient = null, SessionInterface $session = null)
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

        return new ProviderMock(
            $httpClient,
            $session,
            new Consumer(
                'unknown',
                'unkwown'
            ),
            [
                'redirectUri' => 'http://localhost:8000/${provider}/'
            ]
        );
    }

    public function testGetRedirectUrl()
    {
        parent::assertSame('http://localhost:8000/fake/', $this->getAbstractProviderMock()->getRedirectUrl());
    }
}
