<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use SocialConnect\OAuth2\AccessToken;

class GitHubTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\GitHub::class;
    }

    public function testParseTokenSuccess()
    {
        $expectedToken = 'XXXXXXXX';

        $accessToken = $this->getProvider()->parseToken(
            http_build_query(
                [
                    'access_token' => $expectedToken
                ],
                null,
                '&'
            )
        );

        parent::assertInstanceOf(AccessToken::class, $accessToken);
        parent::assertSame($expectedToken, $accessToken->getToken());
    }
}
