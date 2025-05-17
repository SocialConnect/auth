<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenIDConnect;

use SocialConnect\OpenIDConnect\AccessToken;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use Test\AbstractTestCase;

class AccessTokenTest extends AbstractTestCase
{
    public function testConstructSuccess()
    {
        $expectedToken = "XSFJSKLFJDLKFJDLSJFLDSJFDSLFSD";
        $expectedExpires = time();
        $expectedUserId = 123456789;
        $expectedIdToken = 'test';

        $token = new AccessToken(
            [
                'access_token' => $expectedToken,
                'expires' => $expectedExpires,
                'user_id' => $expectedUserId,
                'id_token' => $expectedIdToken,
            ]
        );

        $this->assertSame($expectedToken, $token->getToken());
        $this->assertSame((string) $expectedUserId, $token->getUserId());
        $this->assertSame($expectedExpires, $token->getExpires());
        $this->assertSame($expectedIdToken, $token->getIdToken());

        return $token;
    }

    public function testConstructFailedWithNoIdKey()
    {
        $this->expectException(InvalidAccessToken::class);
        $this->expectExceptionMessage('id_token does not exist inside AccessToken');

        $expectedToken = "XSFJSKLFJDLKFJDLSJFLDSJFDSLFSD";
        $expectedExpires = time();
        $expectedUserId = 123456789;

        new AccessToken([
            'access_token' => $expectedToken,
            'expires' => $expectedExpires,
            'user_id' => $expectedUserId,
        ]);
    }
}
