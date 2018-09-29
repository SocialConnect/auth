<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1\Exception;

/**
 * Class UnknownAuthorization
 */
class UnknownAuthorization extends \SocialConnect\Common\Exception
{
    /**
     * UnknownAuthorization constructor.
     * @param string $message
     */
    public function __construct($message = 'Unknown authorization')
    {
        parent::__construct($message);
    }
}
