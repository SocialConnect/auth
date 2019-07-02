---
layout: home
title: Supported providers
sidebar_label: Supported providers
parent: Documentation
nav_order: 1
---

## Supported providers

The table below lists the social networks currently supported by `socialconnect/auth`:

| Adapter Unique Name | Protocol         | Version  |
|---------------------|------------------|----------|
| [Steam](#steam)     | OpenID           |          |
| Twitter             | OAuth1           |          |
| 500px               | OAuth1           |          |
| Tumblr              | OAuth1           |          |
| Trello              | OAuth1           |          |
| Amazon              | OAuth2           |          |
| Facebook            | OAuth2           | 3.3      |
| Vk (ВКонтакте)      | OAuth2           | 5.100    |
| Instagram           | OAuth2           |          |
| Google              | OAuth2           |          |
| GitHub              | OAuth2           |          |
| GitLab              | OAuth2           |          |
| Slack               | OAuth2           |          |
| BitBucket           | OAuth2           |          |
| Twitch              | OAuth2           |          |
| Vimeo               | OAuth2           |          |
| DigitalOcean        | OAuth2           |          |
| Yandex              | OAuth2           |          |
| MailRu              | OAuth2           |          |
| Microsoft (MSN)     | OAuth2           |          |
| Meetup              | OAuth2           |          |
| [Odnoklassniki](#odnoklassniki)       | OAuth2           |          |
| Discord             | OAuth2           |          |
| SmashCast           | OAuth2           |          |
| Steein              | OAuth2           |          |
| LinkedIn            | OAuth2           |          |
| Yahoo!              | OAuth2           |          |
| Wordpress           | OAuth2           |          |
| Google              | OpenIDConnect    |          |
| PixelIn             | OpenIDConnect    |          |

## User fields compatibility matrix

| Adapter Unique Name | firstName | lastName | fullName | email | avatar | gender | birthday | username | pictureURL |
|---------------------|-----------|----------|----------|-------|--------|--------|----------|----------|------------|
| [Steam](#steam)     | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Twitter             | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| 500px               | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Tumblr              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Trello              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Amazon              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Facebook            | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Vk (ВКонтакте)      | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Instagram           | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Google              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| GitHub              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| GitLab              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Slack               | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| BitBucket           | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Twitch              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Vimeo               | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| DigitalOcean        | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Yandex              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| MailRu              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Microsoft (MSN)     | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Meetup              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| [Odnoklassniki](#odnoklassniki)       | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Discord             | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| SmashCast           | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Steein              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| LinkedIn            | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Yahoo!              | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |
| Wordpress           | ?         | ?        | ?        | ?     | ?      | ?      | ?        | ?        | ?          |

### Steam

Steam is a video game digital distribution platform developed by Valve Corporation. 

| Site                      | ? |
| Protocol                  | OpenID |
| Create App                | [https://steamcommunity.com/dev](https://steamcommunity.com/dev) |
| Documentation             | [https://steamcommunity.com/dev?l=russian](https://steamcommunity.com/dev) |

### Odnoklassniki

| Site                      | ? |
| Protocol                  | OAuth2 |
| Create App                | [http://api.mail.ru/sites/my/add](http://api.mail.ru/sites/my/add) |
| Documentation             | [https://apiok.ru/dev/methods/](https://apiok.ru/dev/methods/) |
