<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Exception;

class UnknownState extends \SocialConnect\Provider\Exception\AuthFailed
{
    public function __construct($message = 'There is no state parameter inside redirect from OAuth provider')
    {
        parent::__construct($message);
    }
}
