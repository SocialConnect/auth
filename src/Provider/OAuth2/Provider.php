<?php
/**
 * Created by PhpStorm.
 * User: ovr
 * Date: 07.09.14
 * Time: 14:28
 */

class Provider
{
    /**
     * @var \SocialConnect\Auth\Service
     */
    public $service;

    public function getRedirectUri()
    {

    }

    public function getBaseUri()
    {
        return 'https://api.vk.com/';
    }

    public function getAuthorizeUri()
    {
        return 'http://api.vk.com/oauth/authorize';
    }

    public function getRequestTokenUri()
    {
        return 'https://api.vk.com/oauth/token';
    }

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