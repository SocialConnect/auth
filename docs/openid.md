---
layout: page
title: OpenID
sidebar_label: OpenID
nav_order: 7
---

`socialconnect/openid` package implements OpenID protocol and provide interfaces and abstract classes that 
allow developers to easily create OpenID clients.

- OpenID v1 (1.1) (WIP!) [Specification](https://openid.net/specs/openid-authentication-1_1.html)
- OpenID v2 [Specification](http://openid.net/specs/openid-authentication-2_0.html)

## Installation

You can install this package via composer:

```sh
$ composer require socialconnect/openid:^3.0
```

## Supported providers

Library has built in support for the following providers:

| Adapter Unique Name             | API Version  |
|---------------------------------|--------------|
| [Steam](#steam)                 |              |
