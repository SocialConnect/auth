<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenID\Provider;

class SteamTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OpenID\Provider\Steam::class;
    }

    public function testParseUserIdFromIdentity()
    {
        $expectedUserId = 76561198066894048;
        $provider = $this->getProvider();

        $result = parent::callProtectedMethod(
            $provider,
            'parseUserIdFromIdentity',
            [
                'http://steamcommunity.com/openid/id/76561198066894048'
            ]
        );

        parent::assertEquals(
            $expectedUserId,
            $result
        );
    }
}
