<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Provider;

use SocialConnect\Auth\Provider\Factory;
use SocialConnect\Auth\Service;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testSuccessFactory()
    {
        $service = new Service(array(), null);

        $vkProvider = Factory::factory('Vk', array(
            'applicationId' => 'test',
            'applicationSecret' => 'test'
        ), $service);
        $this->assertInstanceOf('SocialConnect\Vk\Provider', $vkProvider);
    }
} 