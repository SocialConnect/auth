<?php
/**
 * Copyright (c) Andreas Heigl<andreas@heigl.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright Andreas Heigl
 * @license   http://www.opensource.org/licenses/mit-license.php MIT-License
 * @since     31.03.2017
 * @link      http://github.com/heiglandreas/socialconnect_auth
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
