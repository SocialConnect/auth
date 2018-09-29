<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Exception;

/**
 * Class Unauthorized
 */
class Unauthorized extends \SocialConnect\Provider\Exception\AuthFailed
{
    /**
     * Unauthorized constructor.
     * @param string $message
     */
    public function __construct($message = 'Unauthorized')
    {
        parent::__construct($message);
    }
}
