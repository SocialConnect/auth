<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Provider;

use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Session\SessionInterface;
use Test\TestCase;

class AbstractProviderMock extends AbstractBaseProvider {

    /**
     * @return string
     */
    public function getBaseUri()
    {
        // TODO: Implement getBaseUri() method.
    }

    /**
     * Return Provider's name
     *
     * @return string
     */
    public function getName()
    {
        return 'fake';
    }

    /**
     * @param array $requestParameters
     * @return \SocialConnect\Provider\AccessTokenInterface
     */
    public function getAccessTokenByRequestParameters(array $requestParameters)
    {
        // TODO: Implement getAccessTokenByRequestParameters() method.
    }

    /**
     * @return string
     */
    public function makeAuthUrl()
    {
        // TODO: Implement makeAuthUrl() method.
    }

    /**
     * Get current user identity from social network by $accessToken
     *
     * @param AccessTokenInterface $accessToken
     * @return \SocialConnect\Common\Entity\User
     *
     * @throws \SocialConnect\Provider\Exception\InvalidResponse
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        // TODO: Implement getIdentity() method.
    }
}

class AbstractProviderTest extends TestCase
{
    /**
     * @param ClientInterface|null $httpClient
     * @return AbstractProviderMock
     */
    protected function getAbstractProviderMock(ClientInterface $httpClient = null, SessionInterface $session = null)
    {
        if (!$httpClient) {
            $httpClient = $this->getMockBuilder(\SocialConnect\Common\Http\Client\Curl::class)
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

        return new AbstractProviderMock(
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
