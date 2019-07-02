<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client;

use SocialConnect\Common\Http\Request;

interface ClientInterface
{
    /**
     * Request specify url
     *
     * @param string $url
     * @param array $parameters
     * @param string $method
     * @param array $headers
     * @param array $options
     * @return \SocialConnect\Common\Http\Response
     */
    public function request($url, array $parameters = [], $method = Client::GET, array $headers = [], array $options = []);

    /**
     * @param Request $request
     * @return \SocialConnect\Common\Http\Response
     */
    public function fromRequest(Request $request);
}
