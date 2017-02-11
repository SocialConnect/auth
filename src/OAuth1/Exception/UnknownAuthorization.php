<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1\Exception;

class UnknownAuthorization extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Unknown authorization')
    {
        parent::__construct($message);
    }
}
