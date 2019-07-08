<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Provider;

use Psr\Http\Client\ClientInterface;
use SocialConnect\Provider\Session\SessionInterface;
use Test\AbstractTestCase;

abstract class AbstractProviderTestCase extends AbstractTestCase
{
    /**
     * @return string
     */
    abstract protected function getProviderClassName();

    /**
     * This data is used inside getProvider
     * @return array
     */
    public function getProviderConfiguration(): array
    {
        return [
            'redirectUri' => 'http://localhost:8000/${provider}/',
            'applicationId' => 'applicationId',
            'applicationSecret' => 'applicationSecret',
            'applicationPublic' => 'applicationPublic',
            'scope' => [
                'user',
                'email'
            ]
        ];
    }

    /**
     * @param ClientInterface|null $httpClient
     * @param SessionInterface|null $session
     * @return \SocialConnect\Provider\AbstractBaseProvider
     */
    protected function getProvider(ClientInterface $httpClient = null, SessionInterface $session = null)
    {
        if (!$session) {
            $session = $this->getMockBuilder(\SocialConnect\Provider\Session\Session::class)
                ->disableOriginalConstructor()
                ->disableProxyingToOriginalMethods()
                ->getMock();
        }

        $className = $this->getProviderClassName();

        return new $className(
            $this->getHttpStackMock($httpClient),
            $session,
            $this->getProviderConfiguration()
        );
    }

    /**
     * @param ClientInterface $httpClient
     * @param SessionInterface|null $session
     * @return ProviderMock
     * @throws \SocialConnect\Provider\Exception\InvalidProviderConfiguration
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
            $this->getHttpStackMock($httpClient),
            $session,
            $this->getProviderConfiguration()
        );
    }

    public function testGetRedirectUrl()
    {
        parent::assertSame('http://localhost:8000/fake/', $this->getAbstractProviderMock()->getRedirectUrl());
    }
}
