<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */

namespace SocialConnect\OpenIDConnect\Exception;

use Exception;

class InvalidJWT extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Not Valid JWT', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
