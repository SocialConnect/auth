<?php

/**
 * SocialConnect project
 */
namespace SocialConnect\Provider\Session;

/**
 * Interface SessionInterface
 */
interface SessionInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value);

    /**
     * @param string $key
     */
    public function delete($key);
}
