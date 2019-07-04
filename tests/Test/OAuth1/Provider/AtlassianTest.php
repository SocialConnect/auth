<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace Test\OAuth1\Provider;

use Psr\Http\Client\ClientInterface;
use SocialConnect\Common\Http\Response;
use SocialConnect\OAuth1\Provider\Atlassian;
use SocialConnect\OAuth1\Signature\MethodRSASHA1;
use SocialConnect\Provider\Consumer;
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
        $client = self::getMockBuilder(ClientInterface::class)->getMock();
        $session = self::getMockBuilder(SessionInterface::class)->getMock();

        $configuration = $this->getProviderConfiguration();
        unset($configuration['baseUri']);

        new Atlassian($client, $session, $configuration);
    }

    public function testConstructorHandlesBaseUriWithTrailingSlash()
    {
        $client = self::getMockBuilder(ClientInterface::class)->getMock();
        $session = self::getMockBuilder(SessionInterface::class)->getMock();

        $configuration = $this->getProviderConfiguration();
        $configuration['baseUri'] = 'http://example.com/';

        $provider = new Atlassian($client, $session, $this->getProviderConfiguration());

        $this->assertAttributeEquals('http://example.com', 'baseUri', $provider);
    }

    public function testConstructorHandlesBaseUriWithOutTrailingSlash()
    {
        $client = self::getMockBuilder(ClientInterface::class)->getMock();
        $session = self::getMockBuilder(SessionInterface::class)->getMock();

        $configuration = $this->getProviderConfiguration();
        $configuration['baseUri'] = 'http://example.com';

        $provider = new Atlassian($client, $session, $configuration);

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
