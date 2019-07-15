<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Exception;

use Throwable;

class Unsupported extends \SocialConnect\Common\Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Unsupported functionality', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
