<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Exception;

/**
 * Class UnknownState
 */
class UnknownState extends \SocialConnect\Provider\Exception\AuthFailed
{
    /**
     * UnknownState constructor.
     * @param string $message
     */
    public function __construct($message = 'Unknown state')
    {
        parent::__construct($message);
    }
}
