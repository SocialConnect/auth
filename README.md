SocialConnect Auth
==================

[![Packagist](https://img.shields.io/packagist/v/socialconnect/auth.svg?style=flat-square)](https://packagist.org/packages/socialconnect/auth)
[![License](http://img.shields.io/packagist/l/SocialConnect/auth.svg?style=flat-square)](https://github.com/SocialConnect/auth/blob/master/LICENSE)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/socialconnect/auth/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/SocialConnect/auth/?branch=master)
[![Build Status](http://img.shields.io/travis/SocialConnect/auth.svg?style=flat-square)](https://travis-ci.org/SocialConnect/auth)
[![Scrutinizer Code Coverage](https://img.shields.io/scrutinizer/coverage/g/socialconnect/auth/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/SocialConnect/auth/?branch=master)

## [Documentation](https://socialconnect.lowl.io/) :: [Demo](https://sc.lowl.io/)

> Open source social sign on PHP. Connect your application(s) with social network(s).

Code examples you can find in [example](./example) directory

## Supported type of providers

- [x] OAuth1 [spec RFC 5849](https://tools.ietf.org/html/rfc5849)
- [x] OAuth2 [spec RFC 6749](https://tools.ietf.org/html/rfc6749)
- [X] OpenID v1 (1.1) (WIP!) [spec](https://openid.net/specs/openid-authentication-1_1.html)
- [X] OpenID v2 [spec](http://openid.net/specs/openid-authentication-2_0.html)
- [X] OpenID Connect (1.0) [spec](http://openid.net/specs/openid-connect-core-1_0.html#OpenID.Discovery)
    - [X] JWT (JSON Web Token) [spec RFC 7519](https://tools.ietf.org/html/rfc7519)
    - [X] JWK (JSON Web Keys) [spec RFC 7517](https://tools.ietf.org/html/rfc7517)

## Supported providers

`SocialConnect/Auth` support 30+ providers such as Facebook, Google, Twitter, GitHub, Vk and another.

[See all 30+ provider](https://socialconnect.lowl.io/providers.html)

# Versions

| Version                                                  | Status      | PHP Version |
|----------------------------------------------------------|-------------|-------------|
| [3.x](https://github.com/SocialConnect/auth/tree/3.x)    | Development | >= 7.1      |
| [2.x](https://github.com/SocialConnect/auth/tree/master) | Current     | >= 7.0      |
| [1.x](https://github.com/SocialConnect/auth/tree/1.x)    | Maintenance | >= 5.5      |

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
    "socialconnect/auth": "^2.2.0"
  }
}
```

3. Run `php composer.phar update`

## Referenced projects

- [OrgHeiglHybridAuth](https://github.com/heiglandreas/HybridAuth) from [@heiglandreas](https://github.com/heiglandreas) - Authentication-layer for your ZendFramework3 App.
- [cakephp-social-auth](https://github.com/ADmad/cakephp-social-auth) from [@ADmad](https://github.com/ADmad) - CakePHP plugin. 
- [Phalcon-module-skeleton](https://github.com/ovr/phalcon-module-skeleton) from [@ovr](https://github.com/ovr) - There is a module for Phalcon inside project.

## How to use

Composer:

```sh
composer install 
```

First you need to setup `SocialConnect\Auth\Service`:

```php
$httpClient = new \SocialConnect\Common\Http\Client\Curl();

/**
 * By default we are using Curl class from SocialConnect/Common
 * but you can use Guzzle wrapper ^5.3|^6.0
 */
//$httpClient = new \SocialConnect\Common\Http\Client\Guzzle(
//    new \GuzzleHttp\Client()
//);

/**
 * Why do we need cache decorator for HTTP Client?
 * Providers like OpenID & OpenIDConnect require US
 * to request OpenID specification (and JWK(s) for OpenIDConnect)
 *
 * It's not a best practice to request it every time, because it's unneeded round trip to the server
 * if you are using OpenID or OpenIDConnect we suggest you to use cache
 *
 * If you don`t use providers like (Steam) from OpenID or OpenIDConnect
 * you may skip this because it's not needed
 */
$httpClient = new \SocialConnect\Common\Http\Client\Cache(
    $httpClient,
    /**
     * You can use any library with PSR-16 (simple-cache) compatibility
     */
    new \Symfony\Component\Cache\Psr16Cache(
        new \Symfony\Component\Cache\Adapter\PhpFilesAdapter(
            'socialconnect',
            0,
            __DIR__ . '/cache'
        )
    )
);

$configureProviders = [
    'redirectUri' => 'http://sconnect.local/auth/cb/${provider}/',
    'provider' => [
        'facebook' => [
            'applicationId' => '',
            'applicationSecret' => '',
            'scope' => ['email'],
            'options' => [
                'identity.fields' => [
                    'email',
                    'picture.width(99999)'
                ],
            ],
        ],
    ],
];

$service = new \SocialConnect\Auth\Service(
    $httpClient,
    new \SocialConnect\Provider\Session\Session(),
    $configureProviders
);

/**
 * By default collection factory is null, in this case Auth\Service will create 
 * a new instance of \SocialConnect\Auth\CollectionFactory
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

Contributors
============

This project exists thanks to all the people who contribute. Contributions are welcome!

[![](https://sourcerer.io/fame/ovr/SocialConnect/auth/images/0)](https://sourcerer.io/fame/ovr/SocialConnect/auth/links/0)
[![](https://sourcerer.io/fame/ovr/SocialConnect/auth/images/1)](https://sourcerer.io/fame/ovr/SocialConnect/auth/links/1)
[![](https://sourcerer.io/fame/ovr/SocialConnect/auth/images/2)](https://sourcerer.io/fame/ovr/SocialConnect/auth/links/2)
[![](https://sourcerer.io/fame/ovr/SocialConnect/auth/images/3)](https://sourcerer.io/fame/ovr/SocialConnect/auth/links/3)
[![](https://sourcerer.io/fame/ovr/SocialConnect/auth/images/4)](https://sourcerer.io/fame/ovr/SocialConnect/auth/links/4)
[![](https://sourcerer.io/fame/ovr/SocialConnect/auth/images/5)](https://sourcerer.io/fame/ovr/SocialConnect/auth/links/5)
[![](https://sourcerer.io/fame/ovr/SocialConnect/auth/images/6)](https://sourcerer.io/fame/ovr/SocialConnect/auth/links/6)
[![](https://sourcerer.io/fame/ovr/SocialConnect/auth/images/7)](https://sourcerer.io/fame/ovr/SocialConnect/auth/links/7)

### License

This project is open-sourced software licensed under the MIT License.

See the [LICENSE](LICENSE) file for more information.
