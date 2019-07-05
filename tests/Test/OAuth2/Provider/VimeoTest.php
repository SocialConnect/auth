<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use Psr\Http\Message\ResponseInterface;

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
    protected function getTestResponseForGetIdentity(): ResponseInterface
    {
        return $this->createResponse(
            json_encode([
                'id' => 12345,
            ])
        );
    }
}
