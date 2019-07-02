---
layout: home
title: Home
nav_order: 1
---

# SocialConnect/Auth
{: .fs-9 }

Open-source social sign library for PHP.
{: .fs-6 .fw-300 }

[Get started now](#getting-started){: .btn .btn-primary .fs-5 .mb-4 .mb-md-0 .mr-2 } [Demo](https://sc.lowl.io/){: .btn .fs-5 .mb-4 .mb-md-0 }

## System requirements

Before We start, you should known related requirements for your environment:

- PHP 7.0 or above.
- PHP Sessions (or use stateless mode for OAuth2 and OpenID Connect)
- Curl extension (or you can use stream adapter)
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

### License

SocialConnect projects are distributed by an [MIT license](https://github.com/socialconnect/auth/tree/master/LICENSE).
