<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

class Service
{
    /**
     * @var
     */
    protected $storage;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     * @param $storage
     */
    public function __construct(array $config, $storage)
    {
        $this->config = $config;
        $this->storage = $storage;
    }

    public function getProvider($name)
    {

    }
}
