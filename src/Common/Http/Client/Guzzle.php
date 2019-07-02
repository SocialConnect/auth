<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client;

use \GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use \SocialConnect\Common\Http\Response;

class Guzzle extends Client
{
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client = null)
    {
        $this->client = is_null($client) ? new GuzzleClient() : $client;
    }

    /**
     * {@inheritdoc}
     */
    public function request($uri, array $parameters = array(), $method = Client::GET, array $headers = array(), array $options = array())
    {
        switch ($method) {
            case Client::GET:
                $response = $this->client->get($uri, ['query' => $parameters, 'headers' => $headers]);
                break;
            case Client::POST:
                $response = $this->client->post($uri, ['form_params' => $parameters, 'headers' => $headers]);
                break;
            case Client::PUT:
                $response = $this->client->put($uri, ['form_params' => $parameters, 'headers' => $headers]);
                break;
            case Client::DELETE:
                $response = $this->client->delete($uri, ['query' => $parameters, 'headers' => $headers]);
                break;
            default:
                throw new InvalidArgumentException("Method {$method} is not supported");
        }

        return new Response(
            $response->getStatusCode(),
            (string) $response->getBody(),
            array_map('current', $response->getHeaders())
        );
    }
}
