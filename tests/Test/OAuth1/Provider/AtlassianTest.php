<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace Test\OAuth1\Provider;

use SocialConnect\Common\Http\RequestFactory;
use SocialConnect\Common\Http\Response;
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
        $session = self::getMockBuilder(SessionInterface::class)->getMock();

        $configuration = $this->getProviderConfiguration();
        unset($configuration['baseUri']);

        new Atlassian($this->getHttpStackMock(), $session, $configuration, new RequestFactory());
    }

    public function testConstructorHandlesBaseUriWithTrailingSlash()
    {
        $session = self::getMockBuilder(SessionInterface::class)->getMock();

        $configuration = $this->getProviderConfiguration();
        $configuration['baseUri'] = 'http://example.com/';

        $provider = new Atlassian($this->getHttpStackMock(), $session, $this->getProviderConfiguration(), new RequestFactory());

        $this->assertAttributeEquals('http://example.com', 'baseUri', $provider);
    }

    public function testConstructorHandlesBaseUriWithOutTrailingSlash()
    {
        $session = self::getMockBuilder(SessionInterface::class)->getMock();

        $configuration = $this->getProviderConfiguration();
        $configuration['baseUri'] = 'http://example.com';

        $provider = new Atlassian($this->getHttpStackMock(), $session, $configuration);

        $this->assertAttributeEquals('http://example.com', 'baseUri', $provider);
        $this->assertAttributeInstanceOf(MethodRSASHA1::class, 'signature', $provider);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestResponseForGetIdentity(): Response
    {
        return new Response(
            200,
            [],
            json_encode([
                'name' => 'Dmitry',
            ])
        );
    }
}
