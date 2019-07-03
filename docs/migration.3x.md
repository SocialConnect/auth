---
layout: default
title: Migrating from SocialConnect/Auth 2.x to 3.x
parent: Documentation
nav_order: 2
---

# Migrating from SocialConnect/Auth 2.x to 3.x

# Moving to PSR-16 (simple-cache) compatibility interface for Cache

Generally we stop using `doctrine/cache` for `Common\Http\Client\Cache` and started to use PSR-16 (simple-cache) compatibility providers, 
if you dont use any framework we recommend you to use `symfony/cache` vendor as PSR6 compatibility provider.

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