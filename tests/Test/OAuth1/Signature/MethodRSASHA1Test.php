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

namespace Test\OAuth1\Signature;

use SocialConnect\OAuth1\Request;
use SocialConnect\OAuth1\Signature\MethodRSASHA1;
use SocialConnect\OAuth1\Token;
use SocialConnect\Provider\Consumer;

class MethodRSASHA1Test extends \Test\TestCase
{
    /** @expectedException \InvalidArgumentException */
    public function testConstructorThrowsExceptionOnNonexistnetKey()
    {
        new MethodRSASHA1(__DIR__ . '/nonexistent.pem');
    }

    public function testConstructorWorks()
    {
        $signer = new MethodRSASHA1(__DIR__ . '/../_assets/testkey.pem');
        $this->assertAttributeEquals(
            __DIR__ . '/../_assets/testkey.pem',
            'privateKey',
            $signer
        );
    }

    public function testGettingNameWorks()
    {
        $signer = new MethodRSASHA1(__DIR__ . '/../_assets/testkey.pem');
        $this->assertEquals('RSA-SHA1', $signer->getName());
    }

    public function testCreatingSignatureWorks()
    {
        $signer = new MethodRSASHA1(__DIR__ . '/../_assets/testkey.pem');

        $request = self::getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->method('getSignatureBaseString')->willReturn('baseString');

        $consumer = self::getMockBuilder(Consumer::class)->disableOriginalConstructor()->getMock();
        $consumer->method('getSecret')->willReturn('consumerSecret');

        $token = self::getMockBuilder(Token::class)->disableOriginalConstructor()->getMock();
        $token->method('getSecret')->willReturn('tokenSecret');

        $signature = $signer->buildSignature($request, $consumer, $token);

        $this->assertEquals('HSA65Gc1jqAxgFj2uqiFBMwERymYoPswTS4ij+zc6'
        . 'SMZzw3P5FZDORkUEZ1iTfbZn1dQvLPnIaEIpf1f6sCjdrKFSnA5TIDJC8Gxhyp7YYE8TI'
        . 'XjtyyeAjERiU/nGZLoOlcwUZr2dI65deGiWcLH6x+7JpB5XQJCMZRFuYLZkB4sfbI4Knk'
        . 'e96e0VFjCqHHPFQKZxmgdahWQ+bQFHbMjsNf42uyZVj7ujX78wq8zkCMxaD4etOFOVE0z'
        . 'MKEf23JfcEQqky7NKVwUKp5yuJ5cRjA8IOyVVO9X7+m15q2nTAirR2XyZnoUlyZN5Aquq'
        . 'rFyOP8CROORrv45Uv4F9bBGOA==', $signature);

    }

}
