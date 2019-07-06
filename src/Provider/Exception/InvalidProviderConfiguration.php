<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Provider\Exception;

class InvalidProviderConfiguration extends \SocialConnect\Common\Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
