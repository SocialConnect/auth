<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
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
