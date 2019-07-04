<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use SocialConnect\Common\Http\Response;
use SocialConnect\OAuth2\AccessToken;

class MailRuTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\MailRu::class;
    }

    public function testParseTokenSuccess()
    {
        $expectedToken = 'XXXXXXXX';
        $expectedUserId = 123456;

        $accessToken = $this->getProvider()->parseToken(
            json_encode(
                [
                    'access_token' => $expectedToken,
                    // MailRU uses x_mailru_vid instead of user_id
                    'x_mailru_vid' => $expectedUserId
                ]
            )
        );

        parent::assertInstanceOf(AccessToken::class, $accessToken);
        parent::assertSame($expectedToken, $accessToken->getToken());
        parent::assertSame($expectedUserId, $accessToken->getUserId());
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestResponseForGetIdentity(): Response
    {
        return new Response(
            200,
            [],
            json_encode([
                [
                    'id' => 12345
                ],
            ])
        );
    }
}
