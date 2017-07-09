<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Provider\Exception;

/**
 * This exception is a base exception when we cannot auth user on callback url
 */
abstract class AuthFailed extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Auth failed')
    {
        parent::__construct($message);
    }
}
