<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Exception;

class InvalidResponse extends \SocialConnect\Common\Exception
{
    /**
     * @var mixed
     */
    protected $response;

    public function __construct($message = 'API bad response', $response)
    {
        parent::__construct($message);

        $this->response = $response;
    }
}
