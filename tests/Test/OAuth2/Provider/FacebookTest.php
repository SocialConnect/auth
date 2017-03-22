<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use SocialConnect\OAuth2\AccessToken;

class FacebookTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\Facebook::class;
    }

    /**
     * @todo Test getExpires
     */
    public function testParseTokenSuccess()
    {
        $expectedToken = 'XXXXXXXX';
        $expectedExpires = time() + 60 * 60;

        $accessToken = $this->getProvider()->parseToken(
            http_build_query(
                [
                    'access_token' => $expectedToken,
                    'expires' => $expectedExpires
                ],
                null,
                '&'
            )
        );

        parent::assertInstanceOf(AccessToken::class, $accessToken);
        parent::assertSame($expectedToken, $accessToken->getToken());
    }
}
