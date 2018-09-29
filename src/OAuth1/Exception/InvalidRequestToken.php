<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1\Exception;

/**
 * Class InvalidRequestToken
 */
class InvalidRequestToken extends \SocialConnect\Common\Exception
{
    /**
     * InvalidRequestToken constructor.
     * @param string $message
     */
    public function __construct($message = 'Invalid request token token')
    {
        parent::__construct($message);
    }
}
