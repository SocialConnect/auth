<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Provider;

use SocialConnect\Auth\CollectionFactory;
use SocialConnect\Auth\FactoryInterface;
use SocialConnect\Auth\Service;

class FactoryTest extends \Test\TestCase
{
    public function testSuccessFactory()
    {
        $service = new Service(array(), null);

        $vkProvider = (new CollectionFactory())->factory('Vk', array(
            'applicationId' => 'applicationIdTest',
            'applicationSecret' => 'applicationSecretTest'
        ), $service);

        $this->assertInstanceOf(\SocialConnect\Auth\Provider\Vk::class, $vkProvider);
        $consumer = $vkProvider->getConsumer();

        $this->assertSame('applicationIdTest', $consumer->getKey());
        $this->assertSame('applicationSecretTest', $consumer->getSecret());
    }
}
