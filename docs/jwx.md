---
layout: page
title: JWX Component
sidebar_label: JWX Component
nav_order: 10
---

`socialconnect/jwx` is a library that implements:

- JWT (JSON Web Token) [RFC 7519](https://tools.ietf.org/html/rfc7519)
- JWK (JSON Web Keys) [RFC 7517](https://tools.ietf.org/html/rfc7517)

## Installation

You can install this package via composer:

```sh
$ composer require socialconnect/jwx:^1.0
```

## Encode

```php
<?php

$jwt = new \SocialConnect\JWX\JWT([
    'uid' => 5,
]);

$encodeOptions = new \SocialConnect\JWX\EncodeOptions();
$encodeOptions->setExpirationTime(600);

$token = $jwt->encode('TEST', 'HS256', $encodeOptions);
var_dump($token);
```

## Decode

```php
<?php

$decodeOptions = new \SocialConnect\JWX\DecodeOptions(['HS256'], 'TEST');
$token = \SocialConnect\JWX\JWT::decode($token, $decodeOptions);
var_dump($token);
```
