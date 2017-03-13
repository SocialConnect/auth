<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

class DiscordTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\Discord::class;
    }
}
