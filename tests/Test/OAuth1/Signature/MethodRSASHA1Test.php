<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace Test\OAuth1\Signature;

use SocialConnect\OAuth1\Signature\MethodRSASHA1;
use SocialConnect\OAuth1\Token;
use SocialConnect\Provider\Consumer;
use Test\AbstractTestCase;

class MethodRSASHA1Test extends AbstractTestCase
{
    public function testConstructorThrowsExceptionOnNonexistnetKey()
    {
        $this->expectException(\InvalidArgumentException::class);

        new MethodRSASHA1(__DIR__ . '/nonexistent.pem');
    }

    public function testConstructorWorks()
    {
        $signer = new MethodRSASHA1(__DIR__ . '/../_assets/testkey.pem');
        $this->assertSame(
            __DIR__ . '/../_assets/testkey.pem',
            $this->getClassProperty('privateKey', $signer)
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

        $consumer = $this->getMockBuilder(Consumer::class)->disableOriginalConstructor()->getMock();
        $consumer->method('getSecret')->willReturn('consumerSecret');

        $token = $this->getMockBuilder(Token::class)->disableOriginalConstructor()->getMock();
        $token->method('getSecret')->willReturn('tokenSecret');

        $signature = $signer->buildSignature('baseString', $consumer, $token);

        $this->assertEquals('HSA65Gc1jqAxgFj2uqiFBMwERymYoPswTS4ij+zc6'
        . 'SMZzw3P5FZDORkUEZ1iTfbZn1dQvLPnIaEIpf1f6sCjdrKFSnA5TIDJC8Gxhyp7YYE8TI'
        . 'XjtyyeAjERiU/nGZLoOlcwUZr2dI65deGiWcLH6x+7JpB5XQJCMZRFuYLZkB4sfbI4Knk'
        . 'e96e0VFjCqHHPFQKZxmgdahWQ+bQFHbMjsNf42uyZVj7ujX78wq8zkCMxaD4etOFOVE0z'
        . 'MKEf23JfcEQqky7NKVwUKp5yuJ5cRjA8IOyVVO9X7+m15q2nTAirR2XyZnoUlyZN5Aquq'
        . 'rFyOP8CROORrv45Uv4F9bBGOA==', $signature);
    }
}
