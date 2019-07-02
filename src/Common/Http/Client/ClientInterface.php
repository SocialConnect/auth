<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client;

use SocialConnect\Common\Http\Request;
use SocialConnect\Common\Http\Response;

interface ClientInterface
{
    /**
     * Request specify url
     *
     * @param string $url
     * @param array $options
     * @param array $headers
     * @param string $method
     * @return \SocialConnect\Common\Http\Response
     */
    public function request(string $url, array $options = [], array $headers = [], string $method = Client::GET): Response;

    /**
     * @param Request $request
     * @return \SocialConnect\Common\Http\Response
     */
    public function fromRequest(Request $request);
}
