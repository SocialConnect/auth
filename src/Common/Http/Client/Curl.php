<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client;

use InvalidArgumentException;
use SocialConnect\Common\Http\Client\Response\HeadersParser;
use SocialConnect\Common\Http\Response;
use SocialConnect\Common\Exception;
use RuntimeException;

class Curl extends Client
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
        CURLOPT_USERAGENT => 'SocialConnect\Curl (https://github.com/socialconnect/common) v1.0',
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
     * {@inheritdoc}
     * @throws \SocialConnect\Common\Http\Client\Curl\RequestException
     */
    public function request($uri, array $parameters = array(), $method = Client::GET, array $headers = array(), array $options = array())
    {
        switch ($method) {
            case Client::POST:
                curl_setopt($this->curlHandler, CURLOPT_POST, true);
                break;
            case Client::GET:
                curl_setopt($this->curlHandler, CURLOPT_HTTPGET, true);
                break;
            case Client::DELETE:
            case Client::PATCH:
            case Client::OPTIONS:
            case Client::PUT:
            case Client::HEAD:
                curl_setopt($this->curlHandler, CURLOPT_CUSTOMREQUEST, $method);
                break;
            default:
                throw new InvalidArgumentException("Method {$method} is not supported");
        }

        switch ($method) {
            case Client::POST:
                if (count($parameters) > 0) {
                    $fields = [];
                    foreach ($parameters as $name => $value) {
                        $fields[] = urlencode($name) . '=' . urlencode($value);
                    }

                    curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, implode('&', $fields));
                    unset($fields);
                }
                break;
            default:
                if (count($parameters) > 0) {
                    foreach ($parameters as $key => $parameter) {
                        if (is_array($parameter)) {
                            $parameters[$key] = implode(',', $parameter);
                        }
                    }

                    if (strpos($uri, '?') === false) {
                        $uri .= '?';
                    } else {
                        $uri .= '&';
                    }

                    $uri .= http_build_query($parameters);
                }
                break;
        }

        /**
         * Prepare function for headers like this
         *
         * array('Authorization' => 'token fdsfds')
         */
        if (count($headers) > 0) {
            foreach ($headers as $key => $header) {
                if (!is_int($key)) {
                    $headers[$key] = $key . ': ' . $header;
                }
            }
        }

        /**
         * Setup default parameters
         */
        foreach ($this->parameters as $key => $value) {
            curl_setopt($this->curlHandler, $key, $value);
        }

        $headersParser = new HeadersParser();
        curl_setopt($this->curlHandler, CURLOPT_HEADERFUNCTION, array($headersParser, 'parseHeaders'));
        curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curlHandler, CURLOPT_URL, $uri);

        $result = curl_exec($this->curlHandler);
        if ($result === false) {
            throw new Curl\RequestException(
                curl_error($this->curlHandler),
                curl_errno($this->curlHandler),
                [
                    'uri' => $uri,
                    'method' => $method,
                    'parameters' => $parameters
                ]
            );
        }

        $response = new Response(curl_getinfo($this->curlHandler, CURLINFO_HTTP_CODE), $result, $headersParser->getHeaders());

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

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return resource
     */
    public function getCurlHandler()
    {
        return $this->curlHandler;
    }
}
