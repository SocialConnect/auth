<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenIDConnect;

use SocialConnect\OpenIDConnect\AccessToken;
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
                'user_id' => $expectedUserId,
                'id_token' => 'test'
            ]
        );

        $this->assertSame($expectedToken, $token->getToken());
        $this->assertSame($expectedUserId, $token->getUserId());
        $this->assertSame($expectedExpires, $token->getExpires());

        return $token;
    }

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidAccessToken
     * @expectedExceptionMessage id_token doesnot exists inside AccessToken
     */
    public function testConstructFailedWithNoIdKey()
    {
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
