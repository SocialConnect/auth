<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test;

use SocialConnect\Auth\Service;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Service
     */
    protected function getService()
    {
        return new Service(array(
            'Vk' => array(
                'applicationId' => 123456,
                'applicationSecret' => 'Secret'
            )
        ), null);
    }

    public function testConstructSuccess()
    {
        $service = $this->getService();
        $this->assertTrue(true);
    }

    public function testGetProvider()
    {
        $service = $this->getService();
        $vkProvider = $service->getProvider('Vk');

        $this->assertInstanceOf('SocialConnect\Vk\Provider', $vkProvider);
    }
}
