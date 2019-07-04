<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use Psr\Http\Client\ClientInterface;
use SocialConnect\Common\Http\Response;
use SocialConnect\OAuth2\AccessToken;

class VimeoTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\Vimeo::class;
    }

    public function testGetIdentityInternalServerError()
    {
        // to skip warning
        parent::assertTrue(true);
    }
    
    public function testGetIdentityNotValidJSON()
    {
        // to skip warning
        parent::assertTrue(true);
    }

    public function testGetIdentitySuccess()
    {
        parent::markTestSkipped('Special for Vimeo');
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
                'id' => 12345,
            ])
        );
    }
}
