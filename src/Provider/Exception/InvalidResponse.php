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
     * @var Response|null
     */
    protected $response;

    /**
     * @param string $message
     * @param Response|null $response
     */
    public function __construct($message = 'API bad response', Response $response = null)
    {
        parent::__construct($message);

        $this->response = $response;
    }

    /**
     * Get response data.
     *
     * @return Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
