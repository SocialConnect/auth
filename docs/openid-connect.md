---
layout: page
title: OpenID Connect
parent: Architecture
sidebar_label: OpenID Connect
nav_order: 8
---

`socialconnect/openid-connect` package implements OpenID Connect protocol and provide interfaces and abstract classes that 
allow developers to easily create OpenID Connect clients.

- OpenID Connect (1.0) [Specification](http://openid.net/specs/openid-connect-core-1_0.html#OpenID.Discovery)

Dependencies:

```
socialconnect/provider
socialconnect/oauth2
socialconnect/jwx
```

## Installation

You can install this package via composer:

```sh
$ composer require socialconnect/openid-connect:^3.0
```

## Supported providers

Library has built in support for the following providers:

| Adapter Unique Name             | API Version  |
|---------------------------------|--------------|
| Google                          |              |
| PixelIn                         |              |
| Apple                           |              |
