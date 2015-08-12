<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

return array(
    'redirectUri' => 'http://sconnect.local/auth/cb',
    'provider' => array(
        'Facebook' => array(
            'applicationId' => '',
            'applicationSecret' => '',
            'scope' => array('email')
        ),
        'Twitter' => array(
            'applicationId' => '',
            'applicationSecret' => '',
            'enabled' => false
        ),
        'Google' => array(
            'applicationId' => '',
            'applicationSecret' => '',
            'enabled' => false
        ),
        'Vk' => array(
            'applicationId' => '',
            'applicationSecret' => '',
            'scope' => array('email')
        ),
        'Github' => array(
            'applicationId' => '',
            'applicationSecret'
        ),
        'Instagram' => array(
            'applicationId' => 'ad325fb110b8488da381d575fd1e315f',
            'applicationSecret' => '23c41c2e3e464185a4cb89b6e9668271',
            'scope' => array('basic')
        )
    )
);
