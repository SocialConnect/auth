<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Http\Client;

use SocialConnect\Common\Http\Request;

abstract class Client implements ClientInterface
{
    /**
     * @var string
     */
    const GET = 'GET';

    /**
     * @var string
     */
    const POST = 'POST';

    /**
     * @var string
     */
    const PUT = 'PUT';

    /**
     * @var string
     */
    const PATCH = 'PATCH';

    /**
     * @var string
     */
    const OPTIONS = 'OPTIONS';

    /**
     * @var string
     */
    const HEAD = 'HEAD';

    /**
     * @var string
     */
    const DELETE = 'DELETE';

    /**
     * @param Request $request
     * @return \SocialConnect\Common\Http\Response
     */
    public function fromRequest(Request $request)
    {
        return $this->request(
            $request->getUri(),
            $request->getParameters(),
            $request->getMethod(),
            $request->getHeaders()
        );
    }
}
