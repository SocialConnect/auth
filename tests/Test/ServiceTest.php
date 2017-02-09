<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test;

class ServiceTest extends TestCase
{
    public function testConstructSuccess()
    {
        $service = $this->getService();
        $this->assertInstanceOf(\SocialConnect\Auth\Service::class, $service);
    }

    public function testGetProvider()
    {
        $service = $this->getService();
        $vkProvider = $service->getProvider('Vk');

        $this->assertInstanceOf(\SocialConnect\OAuth2\Provider\Vk::class, $vkProvider);
    }
}
