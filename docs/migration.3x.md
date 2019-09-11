---
layout: page
title: Migrating from SocialConnect/Auth 2.x to 3.x
parent: Documentation
nav_order: 2
---

# Moving to PSR-18 HTTP Client with PSR-7 messaging

Generally we stop `SocialConnect\Common\Http\ClientInterface` from `socialconnect/common` as HTTP-client. We require
any client with PSR-18 compatibility client and PSR-7 as messaging for it.

[Read more about available HTTP clients inside](https://socialconnect.lowl.io/installation.html)

Example with our client:

```sh
$ composer require socialconnect/http-client:^1.0
```

Example:

```php
$httpClient = new \SocialConnect\HttpClient\Curl();

$httpStack = new \SocialConnect\Common\HttpStack(
    // HTTP-client `Psr\Http\Client\ClientInterface`
    $httpClient,
    // RequestFactory that implements Psr\Http\Message\RequestFactoryInterface
    new \SocialConnect\HttpClient\RequestFactory(),
    // StreamFactoryInterface that implements Psr\Http\Message\StreamFactoryInterface
    new \SocialConnect\HttpClient\StreamFactory()
);
```

And pass it to `SocialConnect/Auth` or to provider directly.

```php
$service = new \SocialConnect\Auth\Service(
    $httpStack,
    new \SocialConnect\Provider\Session\Session(),
    $configureProviders,
    $collectionFactory
);
```

# Moving to PSR-16 (simple-cache) compatibility interface for Cache

Generally we stop using `doctrine/cache` for `Common\Http\Client\Cache` and started to use PSR-16 (simple-cache) compatibility providers, 
if you dont use any framework we highly recommend you to use `symfony/cache` vendor as PSR-16 compatibility provider.

```sh
$ composer require symfony/cache
```

Next, You replace you `Http\Client\Cache` creation to:

```php
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
```

# User fields

We hide fields `sex` & `birthday` by protected modifier and changed types:

- `sex` is `female` or `male`, see contacts called: `User::SEX_MALE`/`USER::SEX_FEMALE` (was not strict before)
- `birthday` is `\DateTime` (was string before)

Replace:

```php
$user->sex;
$user->birthday;
```

With:

```php
$user->getSex();
$user->getBirthday();
```
