<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Exception;

class InvalidState extends \SocialConnect\Provider\Exception\AuthFailed
{
    public function __construct($message = 'State parameter inside Request is not similar to value from Session, possible CSRF attack')
    {
        parent::__construct($message);
    }
}
