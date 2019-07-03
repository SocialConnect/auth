<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Common\Http;

interface ResponseInterface
{
    /**
     * Get body of Response
     *
     * @return string|boolean
     */
    public function getBody();

    /**
     * Return status code of Response
     *
     * @return int
     */
    public function getStatusCode();
}
