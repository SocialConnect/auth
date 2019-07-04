<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth1\Provider;

abstract class AbstractProviderTestCase extends \Test\Provider\AbstractProviderTestCase
{
    public function testGetBaseUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getBaseUri());
    }

    public function testGetAuthorizeUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getAuthorizeUri());
    }

    public function testGetRequestTokenUriReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getRequestTokenUri());
    }

    public function testGetNameReturnString()
    {
        parent::assertInternalType('string', $this->getProvider()->getName());
    }
}
