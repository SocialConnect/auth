<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Exception;

class Unauthorized extends \SocialConnect\Provider\Exception\AuthFailed
{
    public function __construct($message = 'Unauthorized')
    {
        parent::__construct($message);
    }
}
