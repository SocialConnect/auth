SocialConnect Auth
==================

[![Packagist](https://img.shields.io/packagist/v/socialconnect/auth.svg?style=flat-square)](https://github.com/socialConnect/auth)
[![License](http://img.shields.io/packagist/l/SocialConnect/auth.svg?style=flat-square)](https://packagist.org/packages/socialconnect/auth)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/socialconnect/auth/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/SocialConnect/auth/?branch=master)
[![Build Status](http://img.shields.io/travis/SocialConnect/auth.svg?style=flat-square)](https://travis-ci.org/SocialConnect/auth)
[![Dependency Status](https://www.versioneye.com/user/projects/54d7935c2bc7901e48000014/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54d7935c2bc7901e48000014)
[![Code Coverage](https://scrutinizer-ci.com/g/SocialConnect/auth/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SocialConnect/auth/?branch=master)

> Open source social sign on PHP. Connect your application(s) with social network(s).

See [example](./example).

If I didn't see your issue, PR please ping me direct by [Telegram](https://telegram.me/ovrweb)!

## Supported type of providers

- [x] OAuth1 [spec RFC 5849](https://tools.ietf.org/html/rfc5849)
- [x] OAuth2 [spec RFC 6749](https://tools.ietf.org/html/rfc6749)
- [X] OpenID v1 (1.1) (WIP!) [spec](https://openid.net/specs/openid-authentication-1_1.html)
- [X] OpenID v2 [spec](http://openid.net/specs/openid-authentication-2_0.html)
- [X] OpenID Connect (1.0) [spec](http://openid.net/specs/openid-connect-core-1_0.html#OpenID.Discovery)
    - [X] JWT (JSON Web Token) [spec RFC 7519](https://tools.ietf.org/html/rfc7519)
    - [X] JWK (JSON Web Keys) [spec RFC 7517](https://tools.ietf.org/html/rfc7517)

## Supported providers

### OpenId

* PayPal (WIP!)
* Steam

### OAuth 1

* Twitter
* 500px
* Tumblr

### OAuth 2

* Amazon
* Facebook
* Vk (ВКонтакте)
* Instagram
* Google
* GitHub
* GitLab
* Slack
* BitBucket
* Twitch
* Vimeo
* DigitalOcean
* Yandex
* MailRu
* Odnoklassniki


#### OpenIDConnect

* Google (you can use Google from `OAuth2` or `OpenIDConnect`)

## Installation

The recommended way to install `socialconnect/auth` is via Composer.

1. If you do not have composer installed, download the [`composer.phar`](https://getcomposer.org/composer.phar) executable or use the installer.

``` sh
$ curl -sS https://getcomposer.org/installer | php
```

2. Run `php composer.phar require socialconnect/auth` or add a new requirement in your composer.json.

``` json
{
  "require": {
    "socialconnect/auth": "~1.0.0"
  }
}
```

3. Run `php composer.phar update`

## How to use

Composer:

```sh
composer install 
```

First you need to setup `SocialConnect\Auth\Service`:

```php
$httpClient = new \SocialConnect\Common\Http\Client\Curl();

/**
 * By default We are using Curl class from SocialConnect/Common
 * but you can use Guzzle wrapper ^5.3|^6.0
 */
//$httpClient = new \SocialConnect\Common\Http\Client\Guzzle(
//    new \GuzzleHttp\Client()
//);

/**
 * Why We need Cache decorator for HTTP Client?
 * Providers like OpenID & OpenIDConnect require US
 * to request OpenID specification (and JWK(s) for OpenIDConnect)
 *
 * It's not a best practise to request it every time, because it's unneeded round trip to the server
 * if you are using OpenID or OpenIDConnect we suggest you to use cache
 *
 * If you don`t use providers like (Steam) from OpenID or OpenIDConnect
 * you may skip this because it's not needed
 */
$httpClient = new \SocialConnect\Common\Http\Client\Cache(
    $httpClient,
    /**
     * Please dont use FilesystemCache for production/stage env, only for local testing!
     * It doesnot support cache expire (remove)
     */
    new \Doctrine\Common\Cache\FilesystemCache(
        __DIR__ . '/cache'
    )
);

$service = new \SocialConnect\Auth\Service(
    $httpClient,
    new \SocialConnect\Provider\Session\Session(),
    $configureProviders
);

$configuration = [
        'redirectUri' => 'http://sconnect.local/auth/cb',
        'provider' => [
            'facebook' => [
                'applicationId' => '',
                'applicationSecret' => '',
                'scope' => [
                    'email'
                ]
            ],
        ]
];

/**
 * By default collection factory is null, in this case Auth\Service will create a new instance of \SocialConnect\Auth\CollectionFactory
 * you can use custom or register another providers by CollectionFactory instance
 */
$collectionFactory = null;

$service = new \SocialConnect\Auth\Service(
    $httpClient,
    new \SocialConnect\Provider\Session\Session(),
    $configureProviders,
    $collectionFactory
);
```

Next create you loginAction:

```php
$providerName = 'facebook';

$provider = $service->getProvider($providerName);
header('Location: ' . $provider->makeAuthUrl());
```

And implement callback handler:

```php
$providerName = 'facebook';

$provider = $service->getProvider($providerName);
$accessToken = $provider->getAccessTokenByRequestParameters($_GET);
var_dump($accessToken);

$user = $provider->getIdentity($accessToken);
var_dump($user);
```

License
-------

This project is open-sourced software licensed under the MIT License.

See the [LICENSE](LICENSE) file for more information.
