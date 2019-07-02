<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Exception;

class Unsupported extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Unsupported functionality', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
