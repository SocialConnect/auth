<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

class FacebookTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\Facebook::class;
    }

    /**
     * @expectedException \SocialConnect\OAuth2\Exception\Unauthorized
     */
    public function testAccessDenied()
    {
        $this->getProvider()->getAccessTokenByRequestParameters(['error' => 'access_denied']);
    }
}
