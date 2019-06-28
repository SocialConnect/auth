---
id:installation
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

To install SocialConnect/Auth, we highly recommend to use Composer.

If Composer is not installed on your system yet, you may go ahead and install it using this command line:

```
sh curl -sS https://getcomposer.org/installer | php
```

```json
{
  "require": {
    "socialconnect/auth": "^2.2.0"
  }
}
```