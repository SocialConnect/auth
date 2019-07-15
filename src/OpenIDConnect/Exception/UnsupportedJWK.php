<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */
declare(strict_types=1);

namespace SocialConnect\OpenIDConnect\Exception;

use Exception;

class UnsupportedJWK extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Unsupported JWK', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
