<?php
/**
 * Created by PhpStorm.
 * User: ovr
 * Date: 07.09.14
 * Time: 14:28
 */

abstract class Provider
{
    /**
     * @var \SocialConnect\Auth\Service
     */
    public $service;

    public function getRedirectUri()
    {

    }

    /**
     * @return string
     */
    abstract public function getBaseUri();

    /**
     * @return string
     */
    abstract public function getAuthorizeUri();

    /**
     * @return string
     */
    abstract public function getRequestTokenUri();

    public function begin()
    {

    }

    public function finish()
    {

    }

    public function requestAccessToken()
    {

    }

    public function getClient()
    {

    }

    public function getUserIdentity()
    {

    }
} 