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
    public function getProviderConsumer(): Consumer
    {
        $consumer = self::getMockBuilder(Consumer::class)->disableOriginalConstructor()->getMock();
        $consumer->method('getKey')->willReturn('key');
        $consumer->method('getSecret')->willReturn(__DIR__ . '/../_assets/testkey.pem');

        return $consumer;
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderConfiguration(): array
    {
        return [
            'redirectUri' => 'http://localhost:8000/',
            'baseUri' => 'http://example.com/'
        ];
    }

    /** @expectedException \InvalidArgumentException */
    public function testConstructorThrowsExceptionOnMissingBaseUri()
    {
        $client = self::getMockBuilder(ClientInterface::class)->getMock();
        $session = self::getMockBuilder(SessionInterface::class)->getMock();
        $consumer = self::getMockBuilder(Consumer::class)->disableOriginalConstructor()->getMock();

        new Atlassian($client, $session, $consumer, []);
    }

    public function testConstructorHandlesBaseUriWithTrailingSlash()
    {
        $client = self::getMockBuilder(ClientInterface::class)->getMock();
        $session = self::getMockBuilder(SessionInterface::class)->getMock();

        $provider = new Atlassian($client, $session, $this->getProviderConsumer(), ['baseUri' => 'http://example.com/']);

        $this->assertAttributeEquals('http://example.com', 'baseUri', $provider);
    }

    public function testConstructorHandlesBaseUriWithOutTrailingSlash()
    {
        $client = self::getMockBuilder(ClientInterface::class)->getMock();
        $session = self::getMockBuilder(SessionInterface::class)->getMock();

        $provider = new Atlassian($client, $session, $this->getProviderConsumer(), ['baseUri' => 'http://example.com']);

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
