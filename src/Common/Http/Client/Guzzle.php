<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

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
    public function request(string $url, array $options = [], array $headers = [], string $method = Client::GET): Response
    {
        switch ($method) {
            case Client::GET:
                $response = $this->client->get($url, ['query' => $options['query'], 'headers' => $headers]);
                break;
            case Client::POST:
                $response = $this->client->post($url, ['form_params' => $options['form'], 'headers' => $headers]);
                break;
            case Client::PUT:
                $response = $this->client->put($url, ['form_params' => $options['form'], 'headers' => $headers]);
                break;
            case Client::DELETE:
                $response = $this->client->delete($url, ['query' => $options['query'], 'headers' => $headers]);
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
