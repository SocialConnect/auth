<?php
/**
 * SocialConnect project
 * @author: Bogdan Popa https://github.com/icex <bogdan@pixelwattstudio.com>
 */

namespace Test\OAuth2\Provider;

use Psr\Http\Message\ResponseInterface;

class LinkedInTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\LinkedIn::class;
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
