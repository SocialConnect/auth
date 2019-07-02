<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client\Curl;

class RequestException extends \RuntimeException
{
    /**
     * @var array
     */
    protected $requestParameters;

    /**
     * RequestException constructor.
     * @param string $message
     * @param int $code
     * @param array $parameters Request parameters
     */
    public function __construct($message, $code, array $parameters)
    {
        parent::__construct($message, $code);

        $this->requestParameters = $parameters;
    }
}
