<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth1\Exception;

class InvalidRequestToken extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Invalid request token token')
    {
        parent::__construct($message);
    }
}
