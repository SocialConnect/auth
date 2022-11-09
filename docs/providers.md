---
layout: page
title: Supported providers
sidebar_label: Supported providers
parent: Documentation
nav_order: 1
---

The table below lists the social networks currently supported. Check the
`example/config.php.dist` file for example configuration for each provider.

| Adapter Unique Name             | Protocol         | API Version  |
|---------------------------------|------------------|--------------|
| [Steam](#steam)                 | OpenID           |              |
| Atlassian                       | OAuth1           |              |
| 500px                           | OAuth1           |              |
| Trello                          | OAuth1           |              |
| Tumblr                          | OAuth1           |              |
| [Twitter](#twitter)             | OAuth1           |              |
| Amazon                          | OAuth2           |              |
| [Facebook](#facebook)           | OAuth2           | 3.3          |
| Vk (ВКонтакте)                  | OAuth2           | 5.100        |
| Instagram                       | OAuth2           |              |
| Google                          | OAuth2           |              |
| GitHub                          | OAuth2           |              |
| GitLab                          | OAuth2           |              |
| Slack                           | OAuth2           |              |
| BitBucket                       | OAuth2           |              |
| Twitch                          | OAuth2           |              |
| Vimeo                           | OAuth2           |              |
| DigitalOcean                    | OAuth2           |              |
| Yandex                          | OAuth2           |              |
| MailRu                          | OAuth2           |              |
| Microsoft (MSN)                 | OAuth2           |              |
| Meetup                          | OAuth2           |              |
| [Odnoklassniki](#odnoklassniki) | OAuth2           |              |
| Discord                         | OAuth2           |              |
| SmashCast                       | OAuth2           |              |
| Steein                          | OAuth2           |              |
| LinkedIn                        | OAuth2           |              |
| Yahoo!                          | OAuth2           |              |
| Wordpress                       | OAuth2           |              |
| Google                          | OpenIDConnect    |              |
| PixelIn                         | OpenIDConnect    |              |
| Keycloak                        | OpenIDConnect    |              |

## User fields compatibility matrix

| Adapter Unique Name   | firstName | lastName | fullName | email | avatar | gender | birthday | username | pictureURL |
|-----------------------|-----------|----------|----------|-------|--------|--------|----------|----------|------------|
| [Steam](#steam)       | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Twitter               | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| 500px                 | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Tumblr                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Trello                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Amazon                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| [Facebook](#facebook) | ✅        | ✅       | ✅        | ✅    | ?      | ✅     | ?        | ?        | ✅          |
| Vk (ВКонтакте)        | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Instagram             | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Google                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| GitHub                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| GitLab                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Slack                 | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| BitBucket             | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Twitch                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Vimeo                 | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| DigitalOcean          | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Yandex                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| MailRu                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Microsoft (MSN)       | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Meetup                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| [Odnoklassniki](#odnoklassniki)       | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Discord               | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| SmashCast             | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Steein                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| LinkedIn              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Yahoo!                | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Wordpress             | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Keycloak              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |

### Steam

Steam is a video game digital distribution platform developed by Valve Corporation.

| Site                      | ? |
| Protocol                  | OpenID |
| Create App                | [https://steamcommunity.com/dev](https://steamcommunity.com/dev) |
| Documentation             | [https://steamcommunity.com/dev](https://steamcommunity.com/dev) |

### Twitter

| Site                      | ? |
| Protocol                  | OAuth1 |
| Create App                | [https://developer.twitter.com/en/apps](https://developer.twitter.com/en/apps) |
| Documentation             | [https://steamcommunity.com/dev](https://steamcommunity.com/dev) |

### Facebook

Facebook, Inc. is an American online social media and social networking service.

| Site                      | [https://www.facebook.com/](https://www.facebook.com/) |
| Protocol                  | OAuth2                    |
| Create App                | [https://developers.facebook.com/apps/](https://developers.facebook.com/apps/) |
| Documentation             | [https://developers.facebook.com/docs/apis-and-sdks](https://developers.facebook.com/docs/apis-and-sdks) |

### Odnoklassniki

| Site                      | ? |
| Protocol                  | OAuth2 |
| Create App                | [http://api.mail.ru/sites/my/add](http://api.mail.ru/sites/my/add) |
| Documentation             | [https://apiok.ru/dev/methods/](https://apiok.ru/dev/methods/) |
