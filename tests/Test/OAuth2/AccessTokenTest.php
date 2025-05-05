<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2;

use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use Test\AbstractTestCase;

class AccessTokenTest extends AbstractTestCase
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
        $this->assertSame((string) $expectedUserId, $token->getUserId());
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
        $this->assertSame((string) $expectedUserId, $token->getUserId());
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

        $expectedUserId = '123456';

        $token->setUserId($expectedUserId);
        $this->assertSame($expectedUserId, $token->getUserId());

        return $token;
    }

    public function testExceptionNotString()
    {
        $this->expectException(InvalidAccessToken::class);
        $this->expectExceptionMessage('API returned data without access_token field');

        new AccessToken([]);
    }
}
