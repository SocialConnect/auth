<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Auth;

use SocialConnect\Auth\CollectionFactory;
use SocialConnect\Auth\Service;

class FactoryTest extends \Test\TestCase
{
    public function testSuccessFactory()
    {
        $service = $this->getService();

        $vkProvider = (new CollectionFactory())->factory('Vk', array(
            'applicationId' => 'applicationIdTest',
            'applicationSecret' => 'applicationSecretTest'
        ), $service);

        $this->assertInstanceOf(\SocialConnect\OAuth2\Provider\Vk::class, $vkProvider);
        $consumer = $vkProvider->getConsumer();

        $this->assertSame('applicationIdTest', $consumer->getKey());
        $this->assertSame('applicationSecretTest', $consumer->getSecret());
    }
}
