<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2;

use SocialConnect\OAuth2\AccessToken;

class AccessTokenTest extends \Test\TestCase
{
    public function testConstructSuccess()
    {
        $expectedToken = "XSFJSKLFJDLKFJDLSJFLDSJFDSLFSD";
        $expectedExpires = time();
        $expectedUserId = 123456789;

        $token = new AccessToken(
            [
                'access_token' => $expectedToken,
                'expires' => $expectedExpires,
                'user_id' => $expectedUserId
            ]
        );

        $this->assertSame($expectedToken, $token->getToken());
        $this->assertSame($expectedUserId, $token->getUserId());
        $this->assertSame($expectedExpires, $token->getExpires());

        return $token;
    }

    public function testConstructSuccessWithExpiresIn()
    {
        $expectedToken = "XSFJSKLFJDLKFJDLSJFLDSJFDSLFSD";
        $expectedExpires = time();
        $expectedUserId = 123456789;

        $token = new AccessToken(
            [
                'access_token' => $expectedToken,
                'expires_in' => $expectedExpires,
                'user_id' => $expectedUserId
            ]
        );

        $this->assertSame($expectedToken, $token->getToken());
        $this->assertSame($expectedUserId, $token->getUserId());
        $this->assertTrue($expectedExpires < $token->getExpires());

        return $token;
    }

    public function testSetUserId()
    {
        $expectedToken = "XSFJSKLFJDLKFJDLSJFLDSJFDSLFSD";

        $token = new AccessToken(
            [
                'access_token' => $expectedToken,
            ]
        );

        $this->assertSame($expectedToken, $token->getToken());
        $this->assertNull($token->getUserId());
        $this->assertNull($token->getExpires());

        $expectedUserId = 123456;

        $token->setUid($expectedUserId);

        $this->assertSame($expectedUserId, $token->getUserId());

        return $token;
    }

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidAccessToken
     * @expectedExceptionMessage API returned data without access_token field
     */
    public function testExceptionNotString()
    {
        new AccessToken([]);
    }
}
