<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Provider\Session;

/**
 * You are not interested to use native PHP sessions and would like to mock without mocking in PHPUnit
 */
class Dummy implements SessionInterface
{
    protected $storage = [];

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->storage[$key])) {
            return $this->storage[$key];
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        if (isset($this->storage[$key])) {
            unset($this->storage[$key]);
        }
    }
}
