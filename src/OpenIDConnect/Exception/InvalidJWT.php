<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */
declare(strict_types=1);

namespace SocialConnect\OpenIDConnect\Exception;

use Throwable;

class InvalidJWT extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Not Valid JWT', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
