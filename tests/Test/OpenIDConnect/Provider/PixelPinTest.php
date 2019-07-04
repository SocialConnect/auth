<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenIDConnect\Provider;

class PixelPinTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OpenIDConnect\Provider\PixelPin::class;
    }
}
