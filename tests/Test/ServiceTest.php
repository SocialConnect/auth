<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test;

use SocialConnect\Auth\Service;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructSuccess()
    {
        $service = new Service(array(
            'Vk' => array(
                'applicationId' => 123456,
                'applicationSecret' => 'Secret'
            )
        ), null);
        $this->assertTrue(true);
    }
}
