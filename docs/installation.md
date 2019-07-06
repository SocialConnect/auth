---
layout: home
title: Installation
sidebar_label: Installation
---

## System requirements

Before We start, you should known related requirements for your environment:

- PHP 7.0 or above.
- PHP Sessions (or use stateless mode for oauth2)
- Curl extension
- JSON extension

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
    "socialconnect/auth": "^3.0"
  }
}
```

## Getting started

## Choosing HTTP Client
 
After installation `socialconnect/auth`, you should decided what HTTP-client you will use, the main requirement that HTTP-client
should be compatibility with PSR-18.

### Using socialconnect/http-client (recommended)

We had `socialconnect/http-client` package, it's our implementation of PSR-18 (HTTP-Client) based on top of `guzzle/psr-7` library.

Run `composer require socialconnect/http-client`

```sh
// You can use any HTTP client with PSR-18 compatibility
$httpClient = new \SocialConnect\HttpClient\Curl();
```

#### Cache for HttpClient (useful for OpenID & OpenIDConnect)

Why do we need cache decorator for HTTP Client?

Providers like OpenID & OpenIDConnect require US to request OpenID specification (and JWK(s) for OpenIDConnect)

It's not a best practice to request it every time, because it's unneeded round trip to the server
if you are using OpenID or OpenIDConnect we suggest you to use cache

If you don`t use providers like (Steam) from OpenID or OpenIDConnect
you may skip this because it's not needed
 
```
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

$httpStack = new \SocialConnect\Provider\HttpStack(
    $httpClient,
    new \SocialConnect\HttpClient\RequestFactory(),
    new \SocialConnect\HttpClient\StreamFactory()
);
```

### Using guzzle

1. `composer require guzzlehttp/guzzle`
2. `composer require php-http/guzzle6-adapter`

```sh
$httpClient = new \Http\Adapter\Guzzle6\Client(
    new \GuzzleHttp\Client()
);

$httpStack = new \SocialConnect\Provider\HttpStack(
    $httpClient,
    new \SocialConnect\HttpClient\RequestFactory(),
    new \SocialConnect\HttpClient\StreamFactory()
);
```

## Configure AuthService

```
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
    $httpStack,
    new \SocialConnect\Provider\Session\Session(),
    $configureProviders,
    $collectionFactory
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
