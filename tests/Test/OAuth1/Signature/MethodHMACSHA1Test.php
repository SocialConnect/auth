<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace Test\OAuth1\Signature;

use SocialConnect\OAuth1\Signature\MethodHMACSHA1;
use SocialConnect\OAuth1\Token;
use SocialConnect\Provider\Consumer;

class MethodHMACSHA1Test extends \Test\TestCase
{
    public function testCreatingSignatureWorks()
    {
        $signer = new MethodHMACSHA1();

        $consumer = self::getMockBuilder(Consumer::class)->disableOriginalConstructor()->getMock();
        $consumer->method('getSecret')->willReturn('consumerSecret');

        $token = self::getMockBuilder(Token::class)->disableOriginalConstructor()->getMock();
        $token->method('getSecret')->willReturn('tokenSecret');

        $signature = $signer->buildSignature('signature', $consumer, $token);

        $this->assertEquals('xG1+MDlpKTVe8iHHtc1fLRM2U1s=', $signature);
    }
}
