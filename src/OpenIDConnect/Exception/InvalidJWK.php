<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */

namespace SocialConnect\OpenIDConnect\Exception;

use Exception;

/**
 * Class InvalidJWK
 */
class InvalidJWK extends \SocialConnect\Common\Exception
{
    /**
     * InvalidJWK constructor.
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = 'Not Valid JWK', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
