<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Common\Http\Client;

use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\Common\Http\Client\Curl\RequestException;
use SocialConnect\Common\Http\Client\Response\HeadersParser;
use SocialConnect\Common\Http\Response;
use SocialConnect\Common\Exception;
use RuntimeException;

class Curl implements ClientInterface
{
    /**
     * Curl resource
     *
     * @var resource
     */
    protected $curlHandler;

    /**
     * @var array
     */
    protected $parameters = array(
        CURLOPT_USERAGENT => 'SocialConnect\Auth (https://github.com/socialconnect/auth) v3',
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 0,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2
    );

    /**
     * @param array|null $parameters
     */
    public function __construct(array $parameters = null)
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('You need to install curl-ext to use SocialConnect-Http\Client\Curl.');
        }

        if ($parameters) {
            $this->parameters = array_replace($this->parameters, $parameters);
        }

        $this->curlHandler = curl_init();
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'POST':
                curl_setopt($this->curlHandler, CURLOPT_POST, true);
                break;
            case 'GET':
                curl_setopt($this->curlHandler, CURLOPT_HTTPGET, true);
                break;
            case 'DELETE':
            case 'PATCH':
            case 'OPTIONS':
            case 'PUT':
            case 'HEAD':
                curl_setopt($this->curlHandler, CURLOPT_CUSTOMREQUEST, $method);
                break;
            default:
                throw new InvalidArgumentException("Method {$method} is not supported");
        }

        if ($request->getBody()->getSize()) {
            curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, $request->getBody()->__toString());
        }

        /**
         * Setup default parameters
         */
        foreach ($this->parameters as $key => $value) {
            curl_setopt($this->curlHandler, $key, $value);
        }

        $headers = [];

        foreach ($request->getHeaders() as $key => $values) {
            $headers[$key] = implode(',', $values);
        }

        $headersParser = new HeadersParser();
        curl_setopt($this->curlHandler, CURLOPT_HEADERFUNCTION, array($headersParser, 'parseHeaders'));
        curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curlHandler, CURLOPT_URL, $request->getUri()->__toString());

        $result = curl_exec($this->curlHandler);
        if ($result === false) {
            throw new Curl\RequestException(
                curl_error($this->curlHandler),
                curl_errno($this->curlHandler),
                [
                    'url' => $request->getUri()->__toString(),
                    'method' => $method,
                ]
            );
        }

        $response = new \GuzzleHttp\Psr7\Response(
            curl_getinfo($this->curlHandler, CURLINFO_HTTP_CODE),
            $headersParser->getHeaders(),
            $result
        );

        /**
         * Reset all options of a libcurl client after request
         */
        curl_reset($this->curlHandler);

        return $response;
    }

    public function __destruct()
    {
        curl_close($this->curlHandler);
    }

    /**
     * @param $option
     * @param $value
     */
    public function setOption($option, $value)
    {
        curl_setopt($this->curlHandler, $option, $value);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
