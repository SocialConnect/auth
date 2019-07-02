<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http;

class Response implements ResponseInterface
{
    const STATUS_OK = 200;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string|boolean
     */
    protected $body;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @param integer $statusCode
     * @param string|null $body
     * @param array $headers
     */
    public function __construct($statusCode, $body, array $headers)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @return string|boolean
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param bool|false $assoc
     * @param int $depth
     * @param int $options
     * @return mixed|null
     */
    public function json($assoc = false, $depth = 512, $options = 0)
    {
        if ($this->body) {
            return json_decode($this->body, $assoc, $depth, $options);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return $this->statusCode == self::STATUS_OK;
    }

    /**
     * Is Server Error? (All 5xx Codes)
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->statusCode > 499 && $this->statusCode < 600;
    }

    /**
     * Does the status code indicate the resource is not found?
     *
     * @return bool
     */
    public function isNotFound()
    {
        return 404 === $this->statusCode;
    }

    /**
     * Was the response successful?
     *
     * @return bool
     */
    public function isSuccess()
    {
        return (200 <= $this->statusCode && 300 > $this->statusCode);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return boolean
     */
    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getHeader($name)
    {
        return $this->headers[$name];
    }
}
