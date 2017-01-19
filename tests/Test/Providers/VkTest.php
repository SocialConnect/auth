<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Providers;

use SocialConnect\Auth\Exception\InvalidAccessToken;
use SocialConnect\Auth\Provider\Consumer;
use SocialConnect\Auth\Provider\OAuth2\AccessToken;
use Test\TestCase;

class VkTest extends TestCase
{
    protected function getProvider()
    {
        $service = new \SocialConnect\Auth\Service([]);

        return new \SocialConnect\Vk\Provider(
            $service,
            new Consumer(
                'unknown',
                'unkwown'
            )
        );
    }

    public function testParseTokenSuccess()
    {
        $expectedToken = 'XXXXXXXX';

        $accessToken = $this->getProvider()->parseToken(
            json_encode(
                [
                    'access_token' => $expectedToken
                ]
            )
        );

        parent::assertInstanceOf(AccessToken::class, $accessToken);
        parent::assertSame($expectedToken, $accessToken->getToken());
    }

    public function testParseTokenNotToken()
    {
        $this->setExpectedException(InvalidAccessToken::class);

        $accessToken = $this->getProvider()->parseToken(
            json_encode([])
        );
    }

    public function testParseTokenNotValidJSON()
    {
        $this->setExpectedException(InvalidAccessToken::class);

        $accessToken = $this->getProvider()->parseToken(
            'lelelelel'
        );
    }
}
