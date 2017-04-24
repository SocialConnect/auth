<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace Test\OAuth1\Provider;

use SocialConnect\Common\Http\Client\ClientInterface;
use SocialConnect\OAuth1\Provider\Atlassian;
use SocialConnect\OAuth1\Signature\AbstractSignatureMethod;
use SocialConnect\OAuth1\Signature\MethodRSASHA1;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Session\SessionInterface;

class AtlassianTest extends \PHPUnit_Framework_TestCase
{
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
        $consumer = self::getMockBuilder(Consumer::class)->disableOriginalConstructor()->getMock();
        $consumer->method('getSecret')->willReturn(__DIR__ . '/../_assets/testkey.pem');

        $provider = new Atlassian($client, $session, $consumer, ['baseUri' => 'http://example.com/']);

        $this->assertAttributeEquals('http://example.com', 'baseUri', $provider);
    }

    public function testConstructorHandlesBaseUriWithOutTrailingSlash()
    {
        $client = self::getMockBuilder(ClientInterface::class)->getMock();
        $session = self::getMockBuilder(SessionInterface::class)->getMock();
        $consumer = self::getMockBuilder(Consumer::class)->disableOriginalConstructor()->getMock();
        $consumer->method('getSecret')->willReturn(__DIR__ . '/../_assets/testkey.pem');

        $provider = new Atlassian($client, $session, $consumer, ['baseUri' => 'http://example.com']);

        $this->assertAttributeEquals('http://example.com', 'baseUri', $provider);
        $this->assertAttributeInstanceOf(MethodRSASHA1::class, 'signature', $provider);
    }
}
