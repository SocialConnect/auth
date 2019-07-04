<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace Test\OAuth1\Provider;

use SocialConnect\Common\Http\Response;

class TumblrTest extends AbstractProviderTestCase
{
    /**
     * @return string
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth1\Provider\Tumblr::class;
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
                'response' => [
                    'user' => [
                        'id' => 12345,
                    ]
                ]
            ])
        );
    }
}
