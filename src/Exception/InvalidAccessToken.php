<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Exception;

class InvalidAccessToken extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Invalid access token')
    {
        parent::__construct($message);
    }
}
