<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Provider\Exception;

class InvalidAccessToken extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Invalid access token')
    {
        parent::__construct($message);
    }
}
