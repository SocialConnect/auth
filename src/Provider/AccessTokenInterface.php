<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Provider;

/**
 * Interface AccessTokenInterface
 */
interface AccessTokenInterface
{
    /**
     * @return string|null
     */
    public function getToken();

    /**
     * @return integer|null
     */
    public function getUserId();

    /**
     * @return integer|null
     */
    public function getExpires();
}
