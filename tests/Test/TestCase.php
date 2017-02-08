<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test;

use PHPUnit_Framework_TestCase;
use SocialConnect\Auth\Service;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected function getService()
    {
        $service = new Service(
            array(
                'provider' => array(
                    'Vk' => array(
                        'applicationId' => 123456,
                        'applicationSecret' => 'Secret'
                    )
                )
            ),
            null
        );

        $httpClient = $this->getMockBuilder(\SocialConnect\Common\Http\Client\Curl::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $service->setHttpClient($httpClient);

        return $service;
    }
}
