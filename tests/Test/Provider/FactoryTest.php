<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\Provider;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testSuccessFactory()
    {
        $vkProvider = \SocialConnect\Auth\Provider\Factory::factory('Vk', array(
            'applicationId' => 'test',
            'applicationSecret' => 'test'
        ));
        $this->assertInstanceOf('SocialConnect\Vk\Provider', $vkProvider);
    }
} 