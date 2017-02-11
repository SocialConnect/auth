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
        $httpClient = $this->getMockBuilder(\SocialConnect\Common\Http\Client\Curl::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $session = $this->getMockBuilder(\SocialConnect\Provider\Session\Session::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $service = new Service(
            $httpClient,
            $session,
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

        return $service;
    }
}
