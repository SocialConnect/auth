<?php
/**
 * SocialConnect project
 * @author: Bogdan Popa https://github.com/icex <bogdan@pixelwattstudio.com>
 */

namespace Test\OAuth2\Provider;

class LinkedInTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\LinkedIn::class;
    }
}
