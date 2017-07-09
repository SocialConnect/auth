<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Exception;

class Unauthorized extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Unauthorized')
    {
        parent::__construct($message);
    }
}
