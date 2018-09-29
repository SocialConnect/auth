<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Exception;

/**
 * Class InvalidState
 */
class InvalidState extends \SocialConnect\Provider\Exception\AuthFailed
{
    /**
     * InvalidState constructor.
     * @param string $message
     */
    public function __construct($message = 'Invalid state')
    {
        parent::__construct($message);
    }
}
