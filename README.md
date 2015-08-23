SocialConnect Auth
==================

[![Build Status](http://img.shields.io/travis/SocialConnect/auth.svg?style=flat-square)](https://travis-ci.org/SocialConnect/auth)
[![Code Coverage](https://scrutinizer-ci.com/g/SocialConnect/auth/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SocialConnect/auth/?branch=master)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/socialconnect/auth/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/SocialConnect/auth/?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/54d7935c2bc7901e48000014/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54d7935c2bc7901e48000014)
[![License](http://img.shields.io/packagist/l/SocialConnect/auth.svg?style=flat-square)](https://packagist.org/packages/socialconnect/auth)

> Connect your application(s) with social network(s).

See [example](./example).

## Supported type of providers

- [x] OAuth1
- [x] OAuth2
- [ ] OpenID (WIP!)

## Supported providers

* Facebook
* GitHub
* Vk (ВКонтакте)
* Instagram
* Twitter
* PayPal (WIP!)

## How to use


First you need to setup service:

```php
$service = new \SocialConnect\Auth\Service(array(
        'redirectUri' => 'http://sconnect.local/auth/cb',
        'provider' => array(
            'Facebook' => array(
                'applicationId' => '',
                'applicationSecret' => '',
                'scope' => array('email')
            ),
        )
));
$service->setHttpClient(new \SocialConnect\Common\Http\Client\Curl());
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

License
-------

This project is open-sourced software licensed under the MIT License.

See the LICENSE file for more information.
