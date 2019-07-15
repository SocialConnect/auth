<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace Test\OAuth1\Provider;

use Psr\Http\Message\ResponseInterface;
use SocialConnect\OAuth1\Provider\Atlassian;
use SocialConnect\OAuth1\Signature\MethodRSASHA1;
use SocialConnect\Provider\Session\SessionInterface;

class AtlassianTest extends AbstractProviderTestCase
{
    /**
     * @return string
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth1\Provider\Atlassian::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderConfiguration(): array
    {
        $configuration = parent::getProviderConfiguration();
        $configuration['applicationSecret'] = __DIR__ . '/../_assets/testkey.pem';
        $configuration['baseUri'] = 'http://example.com/';

        return $configuration;
    }

    /** @expectedException \InvalidArgumentException */
    public function testConstructorThrowsExceptionOnMissingBaseUri()
    {
        $session = $this->getMockBuilder(SessionInterface::class)->getMock();

        $configuration = $this->getProviderConfiguration();
        unset($configuration['baseUri']);

        new Atlassian($this->getHttpStackMock(), $session, $configuration);
    }

    public function testConstructorHandlesBaseUriWithTrailingSlash()
    {
        $session = $this->getMockBuilder(SessionInterface::class)->getMock();

        $configuration = $this->getProviderConfiguration();
        $configuration['baseUri'] = 'http://example.com/';

        $provider = new Atlassian($this->getHttpStackMock(), $session, $configuration);

        $this->assertAttributeEquals('http://example.com', 'baseUri', $provider);
    }

    public function testConstructorHandlesBaseUriWithOutTrailingSlash()
    {
        $session = $this->getMockBuilder(SessionInterface::class)->getMock();

        $configuration = $this->getProviderConfiguration();
        $configuration['baseUri'] = 'http://example.com';

        $provider = new Atlassian($this->getHttpStackMock(), $session, $configuration);

        $this->assertAttributeEquals('http://example.com', 'baseUri', $provider);
        $this->assertAttributeInstanceOf(MethodRSASHA1::class, 'signature', $provider);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestResponseForGetIdentity(): ResponseInterface
    {
        return $this->createResponse(
            json_encode([
                'name' => 'Dmitry',
            ])
        );
    }
}
