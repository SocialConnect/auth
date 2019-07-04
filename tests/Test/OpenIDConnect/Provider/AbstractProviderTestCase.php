<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OpenIDConnect\Provider;

abstract class AbstractProviderTestCase extends \Test\Provider\AbstractProviderTestCase
{
    public function testGetOpenIDUrl()
    {
        parent::assertInternalType('string', $this->getProvider()->getOpenIdUrl());
    }

    public function testGetBaseUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getBaseUri());
    }

    public function testGetNameReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getName());
    }
}
