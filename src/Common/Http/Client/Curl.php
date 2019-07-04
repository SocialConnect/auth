<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Common\Http\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SocialConnect\Common\Http\Client\Exception\ClientException;
use SocialConnect\Common\Http\Client\Exception\NetworkException;
use SocialConnect\Common\Http\Client\Exception\RequestException;
use SocialConnect\Common\Http\Client\Response\HeadersParser;
use SocialConnect\Common\Http\Response;

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
            throw new ClientException('You need to install curl-ext to use SocialConnect-Http\Client\Curl.');
        }

        if ($parameters) {
            $this->parameters = array_replace($this->parameters, $parameters);
        }

        $this->curlHandler = curl_init();
        if ($this->curlHandler === false) {
            throw new ClientException('Unable to init curl');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'HEAD':
                curl_setopt($this->curlHandler, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->curlHandler, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->curlHandler, CURLOPT_POST, true);
                break;
            case 'DELETE':
            case 'PATCH':
            case 'OPTIONS':
            case 'PUT':
            default:
                curl_setopt($this->curlHandler, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }

        curl_setopt($this->curlHandler, CURLOPT_HTTP_VERSION, $this->getProtocolVersion($request));

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

        $errno = curl_errno($this->curlHandler);
        switch ($errno) {
            case CURLE_OK:
                break;
            case CURLE_COULDNT_RESOLVE_PROXY:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_COULDNT_CONNECT:
            case CURLE_OPERATION_TIMEOUTED:
            case CURLE_SSL_CONNECT_ERROR:
            case CURLOPT_DNS_CACHE_TIMEOUT:
            case CURLOPT_TIMEOUT:
                throw new NetworkException(
                    $request,
                    curl_error($this->curlHandler),
                    $errno
                );
            default:
                throw new RequestException(
                    $request,
                    curl_error($this->curlHandler),
                    $errno
                );
        }

        $response = new Response(
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

    /**
     * @param RequestInterface $request
     * @return int
     */
    protected function getProtocolVersion(RequestInterface $request): int
    {
        switch ($request->getProtocolVersion()) {
            case '1.0':
                return CURL_HTTP_VERSION_1_0;
            case '1.1':
                return CURL_HTTP_VERSION_1_1;
            case '2.0':
                if (\defined('CURL_HTTP_VERSION_2_0')) {
                    return CURL_HTTP_VERSION_2_0;
                }

                throw new ClientException('libcurl 7.33 is needed for HTTP 2.0 support');
            default:
                return CURL_HTTP_VERSION_NONE;
        }
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
