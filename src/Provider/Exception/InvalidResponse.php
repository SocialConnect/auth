<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Provider\Exception;

class InvalidResponse extends \SocialConnect\Common\Exception
{
    /**
     * @var mixed
     */
    protected $response;

    /**
     * @param string $message
     * @param mixed $response
     */
    public function __construct($message = 'API bad response', $response = null)
    {
        parent::__construct($message);

        $this->response = $response;
    }

    /**
     * Get response data.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
