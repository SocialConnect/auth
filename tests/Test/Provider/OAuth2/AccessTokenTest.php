<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Provider\OAuth2;

use SocialConnect\Auth\Provider\OAuth2\AccessToken;

class AccessTokenTest extends \Test\TestCase
{
    public function testConstructSuccess()
    {
        $token = new AccessToken('accessToken');
        $this->assertSame('accessToken', $token->getToken());

        return $token;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $token must be a string, passed: integer
     */
    public function testExceptionNotString()
    {
        new AccessToken(12345);
    }
}
