<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Exception;

class UnknownAuthorization extends \SocialConnect\Provider\Exception\AuthFailed
{
    public function __construct($message = 'Unknown authorization')
    {
        parent::__construct($message);
    }
}
