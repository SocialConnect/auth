<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth1;

use SocialConnect\OAuth1\AccessToken;
use Test\AbstractTestCase;

class AccessTokenTest extends AbstractTestCase
{
    public function testConstructMethod()
    {
        $token = new AccessToken([
            'oauth_token' => 'key',
            'oauth_token_secret' => 'secret'
        ]);
        $this->assertEquals('key', $token->getKey());
        $this->assertEquals('secret', $token->getSecret());

        return $token;
    }

    public function testGetUserId()
    {
        $token = $this->testConstructMethod();
        $this->assertNull($token->getUserId());

        $userId = '12345';
        $token->setUserId($userId);

        $this->assertSame($userId, $token->getUserId());
    }
}
