<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Provider\Exception;

/**
 * Class InvalidAccessToken
 */
class InvalidAccessToken extends \SocialConnect\Common\Exception
{
    /**
     * InvalidAccessToken constructor.
     * @param string $message
     */
    public function __construct($message = 'Invalid access token')
    {
        parent::__construct($message);
    }
}
