<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Exception;
/**
 * Class UnknownAuthorization
 */
class UnknownAuthorization extends \SocialConnect\Provider\Exception\AuthFailed
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
