<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth2\Exception;

class InvalidState extends \SocialConnect\Provider\Exception\AuthFailed
{
    public function __construct($message = 'Invalid state')
    {
        parent::__construct($message);
    }
}
