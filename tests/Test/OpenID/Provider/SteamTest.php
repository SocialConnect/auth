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
}
