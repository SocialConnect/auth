<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Provider\Exception;

use SocialConnect\Common\Http\Response;

class InvalidResponse extends \SocialConnect\Common\Exception
{
    /**
     * @var \SocialConnect\Common\Http\Response|null
     */
    protected $response;

    /**
     * @param string $message
     * @param \SocialConnect\Common\Http\Response|null $response
     */
    public function __construct($message = 'API bad response', Response $response = null)
    {
        parent::__construct($message);

        $this->response = $response;
    }

    /**
     * Get response data.
     *
     * @return \SocialConnect\Common\Http\Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
