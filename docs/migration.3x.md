---
layout: default
title: Migrating from SocialConnect/Auth 2.x to 3.x
parent: Documentation
---

# Migrating from SocialConnect/Auth 2.x to 3.x

# Moving to PSR6 compatibility adapter for Cache

Generally we stop using `doctrine/cache` for `Common\Http\Client\Cache` and started to use PSR-6 compatibility provider, if you dont use any framework
we recommend you to use `symfony/cache` vendor as PSR6 compatibility provider.

```sh
$ composer require symfony/cache
```

Next, You replace you `Http\Client\Cache` creation to:

```
$httpClient = new \SocialConnect\Common\Http\Client\Cache(
    $httpClient,
    /**
     * You can use any library with PSR-6 compatibility
     */
    new \Symfony\Component\Cache\Adapter\FilesystemAdapter(
        'socialconnect',
        0,
        __DIR__ . '/cache'
    )
);
```