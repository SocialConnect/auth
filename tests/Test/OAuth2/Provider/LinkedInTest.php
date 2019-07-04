<?php
/**
 * SocialConnect project
 * @author: Bogdan Popa https://github.com/icex <bogdan@pixelwattstudio.com>
 */

namespace Test\OAuth2\Provider;

use SocialConnect\Common\Http\Response;

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
