<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client\Response;

class HeadersParser
{
    /**
     * @var array
     */
    protected $headers = array();

    /**
     * @param resource $client
     * @param string $headerLine
     * @return int
     */
    public function parseHeaders($client, $headerLine)
    {
        $parts = explode(':', $headerLine, 2);
        if (count($parts) == 2) {
            list ($name, $value) = $parts;
            $this->headers[trim($name)] = trim($value);
        }

        return strlen($headerLine);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
